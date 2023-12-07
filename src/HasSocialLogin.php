<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TFSThiagoBR98\LighthouseGraphQLPassport\Exceptions\AccountTerminatedException;
use TFSThiagoBR98\LighthouseGraphQLPassport\Models\SocialProvider;
use TFSThiagoBR98\LighthouseGraphQLPassport\Services\AppleToken;
use Laravel\Socialite\Facades\Socialite;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Trait HasSocialLogin.
 */
trait HasSocialLogin
{
    public function socialProviders(): HasMany
    {
        return $this->hasMany(SocialProvider::class);
    }

    /**
     * @param Request $request
     * @return self
     */
    public static function byOAuthToken(Request $request): self
    {
        if ($request->get('provider') == "apple") {
            $appleToken = new AppleToken(app(Configuration::class));
            config()->set('services.apple.client_secret', $appleToken->generate());

            $refreshToken = $appleToken->fetchRefreshToken($request->get('token'));
        } else {
            $refreshToken = null;
        }

        /** @var AbstractProvider */
        $provider = Socialite::driver($request->get('provider'));
        /** @var \Laravel\Socialite\Two\User */
        $userData = $provider->userFromToken($request->get('token'));

        try {
            $user = static::whereHas('socialProviders', function ($query) use ($request, $userData) {
                $query->where('provider', Str::lower($request->get('provider')))->where('provider_id', $userData->getId());
            })->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if (method_exists(self::class, 'onlyTrashed')) {
                $userDeleted = static::query()
                    ->where('email', $userData->getEmail())
                    ->onlyTrashed()
                    ->first();

                if ($userDeleted != null) {
                    throw new OAuthServerException('Account Terminated.', 400, 'Account Terminated.', previous: new AccountTerminatedException("Account Terminated.", "This account was Terminated"));
                }
            }

            $user = static::where('email', $userData->getEmail())->first();
            if (!$user) {
                $name = $userData->getName();

                if ($name == null) {
                    if ($request->get('name') != null) {
                        $appleName = $request->get('name');
                        $name = trim(
                            ($appleName['firstName'] ?? '')
                                . ' '
                                . ($appleName['lastName'] ?? '')
                        );
                    }
                }

                $valid = Validator::make([
                    'name' => $name
                ], [
                    'name' => ["required", "string", 'full_name'],
                ]);

                if ($valid->fails()) {
                    $name = null;
                }

                $user = static::create([
                    'name' => $name ?? "Sem nome",
                    'email' => $userData->getEmail(),
                    'uuid' => Str::uuid(),
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);
            }

            if ($request->get('provider') == "apple" && $refreshToken != null) {
                SocialProvider::create([
                    'user_id' => $user->getKey(),
                    'provider' => $request->get('provider'),
                    'provider_id' => $userData->getId(),
                    'provider_token' => "RE:" . Crypt::encryptString($request->get('token')) . ":" . $refreshToken,
                ]);
            } else {
                SocialProvider::create([
                    'user_id' => $user->getKey(),
                    'provider' => $request->get('provider'),
                    'provider_id' => $userData->getId(),
                    'provider_token' => Crypt::encryptString($request->get('token')),
                ]);
            }
        }
        Auth::setUser($user);

        return $user;
    }
}
