<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Foreplay\LaravelSdk\Data\AdData;
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Data\BoardData;
use Foreplay\LaravelSdk\Data\BrandData;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBoardAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBoardsRequest;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBrandsByBoardIdRequest;
use Foreplay\LaravelSdk\Pagination\CursorPaginator;

final readonly class BoardResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * The authenticated user's boards. Offset-paginated, limit ≤ 10 per call.
     *
     * @return array<int, BoardData>
     */
    public function all(int $offset = 0, ?int $limit = null): array
    {
        $response = $this->client->send(new GetBoardsRequest($offset, $limit));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $b): BoardData => BoardData::from($b), $items ?? []);
    }

    /**
     * Cursor-paginated ads belonging to a board.
     *
     * @return CursorPaginator<AdData>
     */
    public function ads(string $boardId, ?AdFiltersData $filters = null): CursorPaginator
    {
        return new CursorPaginator(function (?string $cursor) use ($boardId, $filters): array {
            $response = $this->client->send(new GetBoardAdsRequest(
                boardId: $boardId,
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

    /**
     * Brands tracked inside a board. Offset-paginated, limit ≤ 10 per call.
     *
     * @return array<int, BrandData>
     */
    public function brands(string $boardId, int $offset = 0, ?int $limit = null): array
    {
        $response = $this->client->send(new GetBrandsByBoardIdRequest($boardId, $offset, $limit));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $b): BrandData => BrandData::from($b), $items ?? []);
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
