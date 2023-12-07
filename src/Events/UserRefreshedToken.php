<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class UserRefreshedToken.
 */
class UserRefreshedToken
{
    /**
     * @var Authenticatable
     */
    public Authenticatable $user;

    /**
     * UserRefreshedToken constructor.
     *
     * @param  Authenticatable  $user
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
