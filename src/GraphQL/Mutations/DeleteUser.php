<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use TFSThiagoBR98\LighthouseGraphQLPassport\Events\UserDeleted;
use TFSThiagoBR98\LighthouseGraphQLPassport\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Database\Eloquent\Model;

class DeleteUser
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
        if (! Auth::guard('api')->check()) {
            throw new AuthenticationException('Not Authenticated', 'Not Authenticated');
        }
        /** @var Authenticatable|Model */
        $user = Auth::guard('api')->user();
        // revoke user's token
        $user->token()->revoke();

        // delete user
        $user->delete();

        event(new UserDeleted($user));

        return [
            'status'  => 'ACCOUNT_TERMINATED',
            'message' => __('Your account has been terminated'),
        ];
    }
}
