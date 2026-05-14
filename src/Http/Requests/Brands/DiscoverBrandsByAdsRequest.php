<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Brands;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/discovery/brands/explore — distinct brands across ads matching filters.
 * Single-page (no cursor/offset), limit up to 10000.
 * Maps to OpenAPI op discover_brands_by_ads.
 */
final class DiscoverBrandsByAdsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/discovery/brands/explore';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters);
    }
}
