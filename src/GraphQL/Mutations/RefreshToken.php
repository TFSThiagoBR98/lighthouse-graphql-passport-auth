<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use TFSThiagoBR98\LighthouseGraphQLPassport\Events\UserRefreshedToken;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Blake2b;
use Lcobucci\JWT\Signer\Key\InMemory;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RefreshToken.
 */
class RefreshToken extends BaseAuthResolver
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
        $credentials = $this->buildCredentials($args, 'refresh_token');

        $response = $this->makeRequest($credentials);

        // let's get the user id from the new Access token so we can emit an event
        $userId = $this->parseToken($response['access_token']);

        $model = $this->makeAuthModelInstance();

        $user = $model->findOrFail($userId);

        event(new UserRefreshedToken($user));

        return $response;
    }

    /**
     * @param $accessToken
     * @return null|mixed
     */
    public function parseToken($accessToken): mixed
    {
        // since we are generating the token in an internal request, there
        // is no need to verify signature to extract the sub claim
        $config = Configuration::forSymmetricSigner(
            new Blake2b(),
            InMemory::plainText('refresh-token')
        );
        
        $token = $config->parser()->parse((string) $accessToken);
        
        /** @var \Lcobucci\JWT\Token\DataSet */
        $claims = $token->claims();

        return $claims->get('sub');
    }
}
