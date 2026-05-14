<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

final class AdRunningDuration extends Data
{
    public function __construct(
        public readonly ?int $days = null,
    ) {}
}
