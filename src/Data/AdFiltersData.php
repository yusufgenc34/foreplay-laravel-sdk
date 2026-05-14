<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Carbon\CarbonInterface;
use Foreplay\LaravelSdk\Enums\DisplayFormat;
use Foreplay\LaravelSdk\Enums\Language;
use Foreplay\LaravelSdk\Enums\MarketTarget;
use Foreplay\LaravelSdk\Enums\Niche;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\Enums\PublisherPlatform;
use Spatie\LaravelData\Data;

/**
 * Shared filter set used by every paginated ad endpoint.
 *
 * Dates accept Carbon, DateTimeInterface, or a YYYY-MM-DD[ HH:MM:SS] string;
 * the QueryBuilder normalizes them to "Y-m-d H:i:s" in UTC before sending.
 */
final class AdFiltersData extends Data
{
    public function __construct(
        public readonly ?bool $live = null,
        /** @var array<int, DisplayFormat>|null */
        public readonly ?array $display_format = null,
        /** @var array<int, PublisherPlatform>|null */
        public readonly ?array $publisher_platform = null,
        /** @var array<int, Niche>|null */
        public readonly ?array $niches = null,
        /** @var array<int, MarketTarget>|null */
        public readonly ?array $market_target = null,
        /** @var array<int, Language>|null */
        public readonly ?array $languages = null,
        public readonly string|CarbonInterface|\DateTimeInterface|null $start_date = null,
        public readonly string|CarbonInterface|\DateTimeInterface|null $end_date = null,
        public readonly ?int $running_duration_min_days = null,
        public readonly ?int $running_duration_max_days = null,
        public readonly int|float|null $video_duration_min = null,
        public readonly int|float|null $video_duration_max = null,
        public readonly ?Order $order = null,
        public readonly ?int $limit = null,
        public readonly ?string $cursor = null,
    ) {}
}
