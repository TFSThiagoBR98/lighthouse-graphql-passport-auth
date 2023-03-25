<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use Joselfonseca\LighthouseGraphQLPassport\Events\PasswordUpdated;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\ValidationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdatePassword.
 */
class UpdatePassword
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
        /** @var \Illuminate\Contracts\Auth\CanResetPassword|\Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Auth\Authenticatable */
        $user = $context->user();
        if (! Hash::check($args['old_password'], $user->password)) {
            throw new ValidationException([
                'password' => __('Current password is incorrect'),
            ], 'Validation Exception');
        }

        $user->password = Hash::make($args['password']);
        $user->save();

        event(new PasswordUpdated($user));

        return [
            'status'  => 'PASSWORD_UPDATED',
            'message' => __('Your password has been updated'),
        ];
    }
}
