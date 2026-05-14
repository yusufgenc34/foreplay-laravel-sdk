<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Foreplay\LaravelSdk\Data\AdData;
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Data\BrandData;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandRequest;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandsRequest;
use Foreplay\LaravelSdk\Pagination\CursorPaginator;

final readonly class SpyderResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * Brands the authenticated user tracks in Spyder. Offset-paginated, limit ≤ 10 per call.
     *
     * @return array<int, BrandData>
     */
    public function brands(int $offset = 0, ?int $limit = null): array
    {
        $response = $this->client->send(new GetSpyderBrandsRequest($offset, $limit));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $b): BrandData => BrandData::from($b), $items ?? []);
    }

    /**
     * Single Spyder-tracked brand. Throws InvalidApiKeyException if the
     * user doesn't have permission to view the brand.
     */
    public function brand(string $brandId): BrandData
    {
        $response = $this->client->send(new GetSpyderBrandRequest($brandId));

        /** @var array<string, mixed>|null $brand */
        $brand = $response->json('data');

        return BrandData::from($brand ?? []);
    }

    /**
     * Cursor-paginated ads for a Spyder-tracked brand.
     *
     * @return CursorPaginator<AdData>
     */
    public function ads(string $brandId, ?AdFiltersData $filters = null): CursorPaginator
    {
        return new CursorPaginator(function (?string $cursor) use ($brandId, $filters): array {
            $response = $this->client->send(new GetSpyderBrandAdsRequest(
                brandId: $brandId,
                filters: $this->withCursor($filters, $cursor),
            ));

            /** @var array<int, array<string, mixed>>|null $items */
            $items = $response->json('data');
            /** @var string|null $next */
            $next = $response->json('metadata.cursor');

            return [
                'data' => array_map(static fn (array $a): AdData => AdData::from($a), $items ?? []),
                'cursor' => $next,
            ];
        });
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
}
