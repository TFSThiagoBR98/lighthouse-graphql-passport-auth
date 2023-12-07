<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class UserLoggedOut.
 */
class UserLoggedOut
{
    /**
     * @var Authenticatable
     */
    public Authenticatable $user;

    /**
     * UserLoggedOut constructor.
     *
     * @param Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
