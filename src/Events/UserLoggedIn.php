<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class UserLoggedIn.
 */
class UserLoggedIn
{
    /**
     * @var Authenticatable
     */
    public Authenticatable $user;

    /**
     * UserLoggedIn constructor.
     *
     * @param Authenticatable $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
