<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class EmailNotSentException extends Exception implements ClientAware, ProvidesExtensions
{
    /**
     * @var string
     */
    private string $reason;

    public function __construct(string $message, string $reason)
    {
        parent::__construct($message);

        $this->reason = $reason;
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @api
     *
     * @return bool
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return [
            'reason' => $this->reason,
            'is_error' => true,
        ];
    }
}
