<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Prepare2FA
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
            $secret = $user->createTwoFactorAuth();

            return [
                'status' => 'TWO_FACTOR_GENERATED',
                'qrCode' => $secret->toQr(),
                'uri' => $secret->toUri(),
                'string' => $secret->toString(),
            ];
        } else {
            throw new InvalidArgumentException('Two factor is not implemented');
        }
    }
}
