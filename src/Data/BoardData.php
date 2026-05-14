<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * Foreplay user board. Live shape couldn't be captured (test account has no
 * boards); fields below come from public Foreplay docs. Unknown keys are
 * tolerated by spatie/laravel-data and preserved via toArray().
 */
final class BoardData extends Data
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?int $ad_count = null,
        public readonly ?int $brand_count = null,
        public readonly ?string $owner_id = null,
    ) {}
}
