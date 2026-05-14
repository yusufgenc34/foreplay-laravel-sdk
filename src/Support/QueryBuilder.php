<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Support;

use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Foreplay\LaravelSdk\Data\AdFiltersData;

/**
 * Serializes typed inputs into the loose query-array format Saloon's
 * defaultQuery() expects. Centralised so date/bool/enum encoding stays
 * consistent across every Request class in the SDK.
 */
final class QueryBuilder
{
    /**
     * @param  array<string, mixed>  $extras  Optional non-filter params (e.g. brand_ids, query)
     * @return array<string, mixed>
     */
    public static function fromAdFilters(AdFiltersData $filters, array $extras = []): array
    {
        $query = [];

        if ($filters->live !== null) {
            $query['live'] = $filters->live ? 'true' : 'false';
        }

        if ($filters->display_format !== null) {
            $query['display_format'] = self::enumValues($filters->display_format);
        }

        if ($filters->publisher_platform !== null) {
            $query['publisher_platform'] = self::enumValues($filters->publisher_platform);
        }

        if ($filters->niches !== null) {
            $query['niches'] = self::enumValues($filters->niches);
        }

        if ($filters->market_target !== null) {
            $query['market_target'] = self::enumValues($filters->market_target);
        }

        if ($filters->languages !== null) {
            $query['languages'] = self::enumValues($filters->languages);
        }

        if ($filters->start_date !== null) {
            $query['start_date'] = self::date($filters->start_date);
        }

        if ($filters->end_date !== null) {
            $query['end_date'] = self::date($filters->end_date);
        }

        if ($filters->running_duration_min_days !== null) {
            $query['running_duration_min_days'] = $filters->running_duration_min_days;
        }

        if ($filters->running_duration_max_days !== null) {
            $query['running_duration_max_days'] = $filters->running_duration_max_days;
        }

        if ($filters->video_duration_min !== null) {
            $query['video_duration_min'] = $filters->video_duration_min;
        }

        if ($filters->video_duration_max !== null) {
            $query['video_duration_max'] = $filters->video_duration_max;
        }

        if ($filters->order !== null) {
            $query['order'] = $filters->order->value;
        }

        if ($filters->limit !== null) {
            $query['limit'] = $filters->limit;
        }

        if ($filters->cursor !== null && $filters->cursor !== '') {
            $query['cursor'] = $filters->cursor;
        }

        foreach ($extras as $key => $value) {
            if ($value === null) {
                continue;
            }

            $query[$key] = $value instanceof BackedEnum ? $value->value : $value;
        }

        return $query;
    }

    /**
     * @param  array<int, BackedEnum>  $values
     * @return array<int, int|string>
     */
    public static function enumValues(array $values): array
    {
        return array_values(array_map(static fn (BackedEnum $e): int|string => $e->value, $values));
    }

    public static function date(string|CarbonInterface|DateTimeInterface $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->utc()->format('Y-m-d H:i:s');
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)->utc()->format('Y-m-d H:i:s');
        }

        return CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s');
    }
}
