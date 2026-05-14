<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Ads;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/brand/getAdsByPageId?page_id=… — cursor-paginated ads for a FB page ID.
 * Note: page_id is a QUERY parameter, not a path segment.
 * Maps to OpenAPI op get_brands_ads_by_page_id.
 */
final class GetAdsByPageIdRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $pageId,
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/brand/getAdsByPageId';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters, [
            'page_id' => $this->pageId,
        ]);
    }
}
