<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use TFSThiagoBR98\LighthouseGraphQLPassport\Events\ForgotPasswordRequested;
use TFSThiagoBR98\LighthouseGraphQLPassport\Exceptions\EmailNotSentException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ForgotPassword
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
        $response = $this->broker()->sendResetLink(['email' => $args['email']]);

        if ($response == Password::RESET_LINK_SENT) {
            event(new ForgotPasswordRequested($args['email']));

            return [
                'status'  => 'EMAIL_SENT',
                'message' => __($response),
            ];
        }

        throw new EmailNotSentException('Email not sent', __($response));
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker(): PasswordBroker
    {
        return Password::broker();
    }
}
