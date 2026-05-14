<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Boards;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/board/ads — cursor-paginated ads for a specific board.
 * Maps to OpenAPI op get_board_ads.
 */
final class GetBoardAdsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $boardId,
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/board/ads';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters, [
            'board_id' => $this->boardId,
        ]);
    }
}
