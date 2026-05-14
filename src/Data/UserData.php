<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

final class UserData extends Data
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $email = null,
    ) {}
}
