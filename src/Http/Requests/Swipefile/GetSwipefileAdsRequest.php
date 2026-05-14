<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Swipefile;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/swipefile/ads — offset-paginated ads saved in the user's swipefile.
 * `order=saved_newest` is the most useful default for swipefiles.
 * Maps to OpenAPI op get_swipefile_ads.
 */
final class GetSwipefileAdsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly AdFiltersData $filters = new AdFiltersData,
        public readonly int $offset = 0,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/swipefile/ads';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        // Swipefile uses offset instead of cursor; drop any stale cursor and
        // surface offset as a top-level query param.
        return QueryBuilder::fromAdFilters($this->filters, [
            'offset' => $this->offset,
        ]);
    }
}
