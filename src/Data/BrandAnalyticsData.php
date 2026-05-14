<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * One day of running-ads + creative-velocity counters for a brand or page.
 * Returned by /api/brand/analytics. brand_id / page_id / domain / page_name
 * are echoed back by some accounts but elided when constant for the request,
 * so they are kept optional.
 */
final class BrandAnalyticsData extends Data
{
    public function __construct(
        public readonly ?string $date = null,
        public readonly ?string $page_id = null,
        public readonly ?string $page_name = null,
        public readonly ?string $domain = null,
        public readonly ?string $brand_id = null,
        public readonly ?int $active_count = null,
        public readonly ?int $inactive_count = null,
        public readonly ?int $dco = null,
        public readonly ?int $video = null,
        public readonly ?int $image = null,
        public readonly ?int $dpa = null,
        public readonly ?int $carousel = null,
        public readonly ?int $multi_images = null,
        public readonly ?int $multi_videos = null,
        public readonly ?int $multi_medias = null,
        public readonly ?int $page_like = null,
        public readonly ?int $event = null,
        public readonly ?int $text = null,
    ) {}
}
