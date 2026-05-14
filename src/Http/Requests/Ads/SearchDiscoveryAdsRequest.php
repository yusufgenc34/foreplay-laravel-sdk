<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Ads;

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/discovery/ads — cursor-paginated text+filter search across all ads.
 * Maps to MCP tool search_discovery_ads.
 */
final class SearchDiscoveryAdsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly ?string $searchQuery = null,
        public readonly AdFiltersData $filters = new AdFiltersData,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/discovery/ads';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return QueryBuilder::fromAdFilters($this->filters, [
            'query' => $this->searchQuery !== null && $this->searchQuery !== '' ? $this->searchQuery : null,
        ]);
    }
}
