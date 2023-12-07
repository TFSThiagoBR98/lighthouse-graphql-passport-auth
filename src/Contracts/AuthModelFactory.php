<?php

declare(strict_types=1);

namespace TFSThiagoBR98\LighthouseGraphQLPassport\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuthModelFactory
{
    public function make(array $attributes = []): Model;

    public function create(array $attributes = []): Model;

    public function getClass(): string;
}
