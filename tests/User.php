<?php

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use TFSThiagoBR98\LighthouseGraphQLPassport\HasLoggedInTokens;
use TFSThiagoBR98\LighthouseGraphQLPassport\HasSocialLogin;
use Laravel\Passport\HasApiTokens;

/**
 * Class User.
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use HasLoggedInTokens;
    use HasSocialLogin;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
