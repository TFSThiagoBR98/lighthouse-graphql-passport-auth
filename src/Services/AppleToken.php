<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT\Configuration;
use SocialiteProviders\Apple\Provider;

class AppleToken
{
    private Configuration $jwtConfig;

    public function __construct(Configuration $jwtConfig)
    {
        $this->jwtConfig = $jwtConfig;
    }

    /**
     * Get Apple provider
     *
     * @return Provider
     */
    public function getProvider(): Provider
    {
        return Socialite::driver('apple');
    }

    /**
     * Validate the refresh token
     *
     * @param string $token
     * @return string|null
     */
    public function validateRefreshToken(string $token): ?string
    {
        $appleToken = new AppleToken(app(Configuration::class));
        config()->set('services.apple.client_secret', $appleToken->generate());

        $client = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode(config('services.apple.client_id') . ':' . config('services.apple.client_secret')),
                'user-agent' => 'BRTec/RotativoDigital Server 1.0.0'
            ])
            ->acceptJson()
            ->asForm();
        $response = $client->post(
            'https://appleid.apple.com/auth/token',
            [
                'client_id' => config('services.apple.client_id'),
                'client_secret' => config('services.apple.client_secret'),
                'grant_type' => 'refresh_token',
                'token' => Crypt::decryptString($token),
            ]
        );

        if ($response->successful()) {
            return $response->json('id_token');
        } else {
            return null;
        }
    }

    /**
     * Break token for test
     *
     * @param string $token
     * @return array
     */
    public function breakToken(string $token): ?array {
        if (!str_starts_with($token, 'RE:')) {
            return null;
        }
        $blocks = explode(':', $token);
        return [
            'id_token' => $blocks[1],
            'refresh_token' => $blocks[2]
        ];
    }

    /**
     * Fetch refresh token from API
     *
     * @param string $token
     * @return string|null
     */
    public function fetchRefreshToken(string $token): ?string
    {
        $appleToken = new AppleToken(app(Configuration::class));
        config()->set('services.apple.client_secret', $appleToken->generate());

        try {
            $response = $this->getProvider()->getAccessTokenResponse($token);
            return Crypt::encryptString($response['refresh_token']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function revokeToken(string $token): ?bool
    {
        $client = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode(config('services.apple.client_id') . ':' . config('services.apple.client_secret')),
                'user-agent' => 'BRTec/RotativoDigital Server 1.0.0'
            ])
            ->acceptJson()
            ->asForm();
        $response = $client->post(
            'https://appleid.apple.com/auth/revoke',
            [
                'client_id' => config('services.apple.client_id'),
                'client_secret' => config('services.apple.client_secret'),
                'token' => Crypt::decryptString($token),
            ]
        );

        return $response->successful();
    }

    public function generate()
    {
        $now = CarbonImmutable::now();

        $token = $this->jwtConfig->builder()
            ->issuedBy(config('services.apple.team_id'))
            ->issuedAt($now)
            ->expiresAt($now->addHour())
            ->permittedFor('https://appleid.apple.com')
            ->relatedTo(config('services.apple.client_id'))
            ->withHeader('kid', config('services.apple.key_id'))
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        return $token->toString();
    }
}
