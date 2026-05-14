<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Foreplay\LaravelSdk\Data\AdData;
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdDuplicatesRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdsByBrandIdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdsByPageIdRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\SearchDiscoveryAdsRequest;
use Foreplay\LaravelSdk\Pagination\CursorPaginator;
use Saloon\Http\Request;

final readonly class AdResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * Single ad by ID. Throws EndpointNotFoundException for unknown IDs.
     */
    public function get(string $adId): AdData
    {
        $response = $this->client->send(new GetAdRequest($adId));

        /** @var array<string, mixed>|null $data */
        $data = $response->json('data');

        return AdData::from($data ?? []);
    }

    /**
     * Group of ads sharing the same creative as the given ad.
     *
     * @return array<int, AdData>
     */
    public function duplicates(string $adId): array
    {
        $response = $this->client->send(new GetAdDuplicatesRequest($adId));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $item): AdData => AdData::from($item), $items ?? []);
    }

    /**
     * Cursor-paginated ads for one or more brand IDs.
     *
     * @param  array<int, string>  $brandIds
     * @return CursorPaginator<AdData>
     */
    public function byBrandIds(array $brandIds, ?AdFiltersData $filters = null): CursorPaginator
    {
        return $this->cursorPaginator(
            fn (?string $cursor) => new GetAdsByBrandIdsRequest(
                brandIds: $brandIds,
                filters: $this->withCursor($filters, $cursor),
            )
        );
    }

    /**
     * Cursor-paginated ads for a Facebook page ID.
     *
     * @return CursorPaginator<AdData>
     */
    public function byPageId(string $pageId, ?AdFiltersData $filters = null): CursorPaginator
    {
        return $this->cursorPaginator(
            fn (?string $cursor) => new GetAdsByPageIdRequest(
                pageId: $pageId,
                filters: $this->withCursor($filters, $cursor),
            )
        );
    }

    /**
     * Cursor-paginated text+filter search across the whole ad database.
     *
     * @return CursorPaginator<AdData>
     */
    public function search(?string $query = null, ?AdFiltersData $filters = null): CursorPaginator
    {
        return $this->cursorPaginator(
            fn (?string $cursor) => new SearchDiscoveryAdsRequest(
                searchQuery: $query,
                filters: $this->withCursor($filters, $cursor),
            )
        );
    }

    private function withCursor(?AdFiltersData $filters, ?string $cursor): AdFiltersData
    {
        $base = $filters ?? new AdFiltersData;

        if ($cursor === null) {
            return $base;
        }

        return new AdFiltersData(
            live: $base->live,
            display_format: $base->display_format,
            publisher_platform: $base->publisher_platform,
            niches: $base->niches,
            market_target: $base->market_target,
            languages: $base->languages,
            start_date: $base->start_date,
            end_date: $base->end_date,
            running_duration_min_days: $base->running_duration_min_days,
            running_duration_max_days: $base->running_duration_max_days,
            video_duration_min: $base->video_duration_min,
            video_duration_max: $base->video_duration_max,
            order: $base->order,
            limit: $base->limit,
            cursor: $cursor,
        );
    }

    /**
     * @param  callable(?string $cursor): Request  $requestFactory
     * @return CursorPaginator<AdData>
     */
    private function cursorPaginator(callable $requestFactory): CursorPaginator
    {
        return new CursorPaginator(function (?string $cursor) use ($requestFactory): array {
            $response = $this->client->send($requestFactory($cursor));

            /** @var array<int, array<string, mixed>>|null $items */
            $items = $response->json('data');
            /** @var string|null $nextCursor */
            $nextCursor = $response->json('metadata.cursor');

            return [
                'data' => array_map(static fn (array $item): AdData => AdData::from($item), $items ?? []),
                'cursor' => $nextCursor,
            ];
        });
    }
}
