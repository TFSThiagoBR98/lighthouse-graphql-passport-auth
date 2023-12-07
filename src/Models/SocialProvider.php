<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TFSThiagoBR98\LighthouseGraphQLPassport\Contracts\AuthModelFactory;

class SocialProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo($this->getAuthModelFactory()->getClass());
    }

    protected function getAuthModelFactory(): AuthModelFactory
    {
        return app(AuthModelFactory::class);
    }
}
