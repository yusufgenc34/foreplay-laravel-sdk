<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * Foreplay ad payload. Shape mirrors the AdResponse example documented
 * in the Foreplay MCP schemas — only fields explicitly observed there
 * are typed. Unknown extras pass through via toArray()/getRaw().
 */
final class AdData extends Data
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $ad_id = null,
        public readonly ?string $name = null,
        public readonly ?string $brand_id = null,
        public readonly ?string $brand_name = null,
        public readonly ?string $description = null,
        public readonly ?string $cta_title = null,
        public readonly ?string $cta_type = null,
        /** @var array<int, string>|null */
        public readonly ?array $categories = null,
        public readonly ?string $creative_targeting = null,
        /** @var array<int, string>|null */
        public readonly ?array $languages = null,
        public readonly ?string $market_target = null,
        /** @var array<int, string>|null */
        public readonly ?array $niches = null,
        public readonly ?string $product_category = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $timestamped_transcription = null,
        public readonly ?string $full_transcription = null,
        /** @var array<int, mixed>|null */
        public readonly ?array $cards = null,
        public readonly ?string $avatar = null,
        public readonly ?string $display_format = null,
        /** @var array<string, mixed>|null EmotionalDrivers model */
        public readonly ?array $emotional_drivers = null,
        public readonly ?string $link_url = null,
        public readonly ?bool $live = null,
        /** @var array<string, mixed>|null PersonaModel */
        public readonly ?array $persona = null,
        /** @var array<int, string>|null */
        public readonly ?array $publisher_platform = null,
        public readonly ?int $started_running = null,
        public readonly ?string $thumbnail = null,
        public readonly int|float|null $time_product_was_mentioned = null,
        public readonly ?string $type = null,
        public readonly ?string $video = null,
        public readonly ?string $image = null,
        /** ContentFilterModel | string | bool */
        public readonly mixed $content_filter = null,
        public readonly ?AdRunningDuration $running_duration = null,
        public readonly ?string $title = null,
        public readonly ?string $headline = null,
        public readonly ?string $url = null,
        public readonly ?string $ad_library_url = null,
        public readonly ?float $video_duration = null,
    ) {}
}
