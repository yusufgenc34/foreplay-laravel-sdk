<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Brands;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/discovery/brands — fuzzy brand-name search.
 * Maps to OpenAPI op search_discovery_brands.
 */
final class SearchDiscoveryBrandsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $searchQuery,
        public readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/discovery/brands';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $q = ['query' => $this->searchQuery];

        if ($this->limit !== null) {
            $q['limit'] = $this->limit;
        }

        return $q;
    }
}
