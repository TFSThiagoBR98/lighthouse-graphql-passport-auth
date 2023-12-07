<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasLoggedInTokens
{
    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function getTokens(): array
    {
        $request = Request::create('oauth/token', 'POST', [
            'grant_type'    => 'logged_in_grant',
            'client_id'     => config('lighthouse-graphql-passport.client_id'),
            'client_secret' => config('lighthouse-graphql-passport.client_secret'),
        ], [], [], [
            'HTTP_Accept' => 'application/json',
        ]);
        $response = app()->handle($request);

        return json_decode($response->getContent(), true);
    }

    /**
     * @param mixed $request
     * @return mixed
     */
    public function byLoggedInUser(mixed $request): ?Authenticatable
    {
        return Auth::user();
    }
}
