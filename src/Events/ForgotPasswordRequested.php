<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Events;

/**
 * Class ForgotPasswordRequested.
 */
class ForgotPasswordRequested
{
    /**
     * @var string
     */
    public string $email;

    /**
     * ForgotPasswordRequested constructor.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }
}
