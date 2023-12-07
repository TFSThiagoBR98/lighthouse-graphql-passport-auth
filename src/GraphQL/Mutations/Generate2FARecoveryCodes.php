<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use TFSThiagoBR98\LighthouseGraphQLPassport\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use TFSThiagoBR98\LighthouseGraphQLPassport\Exceptions\ValidationException;

class Generate2FARecoveryCodes
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
    public function __invoke($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo): array
    {
        if (! Auth::guard('api')->check()) {
            throw new AuthenticationException('Not Authenticated', 'Not Authenticated');
        }
        /** @var Authenticatable|Model|TwoFactorAuthenticatable */
        $user = Auth::guard('api')->user();
        
        if ($user instanceof TwoFactorAuthenticatable) {
            return [
                'status' => 'TWO_FACTOR_RECOVERY_REGENERATED',
                'recoveryCodes' => $user->generateRecoveryCodes(),
            ];
        } else {
            throw new InvalidArgumentException('Two factor is not implemented');
        }
    }
}
