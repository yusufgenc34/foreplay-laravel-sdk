<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * /api/usage payload — credit window + connected account.
 */
final class UsageData extends Data
{
    public function __construct(
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?int $total_credits = null,
        public readonly ?int $remaining_credits = null,
        public readonly ?UserData $user = null,
    ) {}
}
