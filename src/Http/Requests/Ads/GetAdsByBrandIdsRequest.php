<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Ads;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/brand/getAdsByBrandId — cursor-paginated ads for one or more brand IDs.
 * Maps to OpenAPI op get_ads_by_brand_ids.
 */
final class GetAdsByBrandIdsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  array<int, string>  $brandIds
     */
    public function __construct(
        public readonly array $brandIds,
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/brand/getAdsByBrandId';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters, [
            'brand_ids' => array_values($this->brandIds),
        ]);
    }
}
