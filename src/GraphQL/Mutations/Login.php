<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Joselfonseca\LighthouseGraphQLPassport\Events\UserLoggedIn;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\AuthenticationException;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login extends BaseAuthResolver
{
    /**
     * @param $rootValue
     * @param array $args
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null $context
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo): array
    {
        $credentials = $this->buildCredentials($args);
        $response = $this->makeRequest($credentials);
        $user = $this->findUser($args['username']);

        $this->validateUser($user);

        if ($user instanceof TwoFactorAuthenticatable) {
            $code = $args['otp'] ?? null;

            if ($user->hasTwoFactorEnabled() && (! $user->validateTwoFactorCode($code))) {
                throw new AuthenticationException(__('Authentication exception'), __('Invalid two factor code'));
            }
        }

        event(new UserLoggedIn($user));

        return array_merge(
            $response,
            [
                'user' => $user,
            ]
        );
    }

    protected function validateUser(Model|Authenticatable $user): void
    {
        $authModelClass = $this->getAuthModelClass();
        if ($user instanceof $authModelClass && $user->exists) {
            return;
        }

        throw (new ModelNotFoundException())
            ->setModel($authModelClass);
    }
    
    protected function findUser(string $username): Model|Authenticatable
    {
        $model = $this->makeAuthModelInstance();

        if (method_exists($model, 'findForPassport')) {
            return $model->findForPassport($username);
        }

        return $model::query()
            ->where(config('lighthouse-graphql-passport.username'), $username)
            ->first();
    }
}
