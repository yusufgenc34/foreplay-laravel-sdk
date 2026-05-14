<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * Foreplay brand payload. Shape verified against live responses from
 * /api/brand/getBrandsByDomain, /api/discovery/brands, /api/discovery/brands/explore,
 * /api/spyder/brands and /api/board/brands.
 */
final class BrandData extends Data
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        /** @var array<string, mixed>|string|null  description is usually {"text": "..."} */
        public readonly array|string|null $description = null,
        public readonly ?string $category = null,
        /** @var array<int, string>|null */
        public readonly ?array $niches = null,
        public readonly ?string $verification_status = null,
        public readonly ?string $url = null,
        /** @var array<int, string>|null */
        public readonly ?array $websites = null,
        public readonly ?string $avatar = null,
        public readonly ?string $ad_library_id = null,
        public readonly ?bool $is_delegate_page_with_linked_primary_profile = null,
    ) {}
}
