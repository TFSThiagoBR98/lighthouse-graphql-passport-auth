<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport;

use Joselfonseca\LighthouseGraphQLPassport\Notifications\VerifyEmail;

trait MustVerifyEmailGraphQL
{
    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }
}
