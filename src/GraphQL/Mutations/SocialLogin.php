<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SocialLogin.
 */
class SocialLogin extends BaseAuthResolver
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
        $credentials = $this->buildCredentials($args, 'social_grant');
        $response = $this->makeRequest($credentials);
        $model = $this->makeAuthModelInstance();
        $user = $model->findOrFail(Auth::id());
        $response['user'] = $user;

        return $response;
    }
}
