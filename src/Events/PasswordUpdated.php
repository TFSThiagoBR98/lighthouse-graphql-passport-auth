<?php

declare(strict_types=1);

namespace Joselfonseca\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PasswordUpdated.
 */
class PasswordUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var Authenticatable
     */
    public mixed $user;

    /**
     * PasswordUpdated constructor.
     *
     * @param Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
