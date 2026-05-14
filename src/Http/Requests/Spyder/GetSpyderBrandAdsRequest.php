<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Spyder;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/spyder/brand/ads?brand_id=… — cursor-paginated ads for a Spyder-tracked brand.
 * Maps to OpenAPI op get_spyder_brand_ads.
 */
final class GetSpyderBrandAdsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $brandId,
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/spyder/brand/ads';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters, [
            'brand_id' => $this->brandId,
        ]);
    }
}
