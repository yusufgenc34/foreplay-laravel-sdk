<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Data\BrandAnalyticsData;
use Foreplay\LaravelSdk\Data\BrandData;
use Foreplay\LaravelSdk\Enums\BrandSortOrder;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Brands\DiscoverBrandsByAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\GetBrandsAnalyticsRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\GetBrandsByDomainRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\SearchDiscoveryBrandsRequest;
use Saloon\Http\Response;

final readonly class BrandResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * Brands matching a domain (full URL or bare host).
     *
     * @return array<int, BrandData>
     */
    public function byDomain(string $domain, ?int $limit = null, ?BrandSortOrder $order = null): array
    {
        return $this->mapBrands(
            $this->client->send(new GetBrandsByDomainRequest($domain, $limit, $order))
        );
    }

    /**
     * Fuzzy brand-name search.
     *
     * @return array<int, BrandData>
     */
    public function search(string $query, ?int $limit = null): array
    {
        return $this->mapBrands(
            $this->client->send(new SearchDiscoveryBrandsRequest($query, $limit))
        );
    }

    /**
     * Distinct brands across ads matching the given filters (limit up to 10000).
     *
     * @return array<int, BrandData>
     */
    public function discoverByAds(?AdFiltersData $filters = null): array
    {
        return $this->mapBrands(
            $this->client->send(new DiscoverBrandsByAdsRequest($filters ?? new AdFiltersData))
        );
    }

    /**
     * Daily ad-distribution + creative-velocity rows for a brand or page (≤30 days).
     *
     * @return array<int, BrandAnalyticsData>
     */
    public function analytics(
        string $id,
        string|CarbonInterface|DateTimeInterface|null $startDate = null,
        string|CarbonInterface|DateTimeInterface|null $endDate = null,
        ?Order $order = null,
    ): array {
        $response = $this->client->send(new GetBrandsAnalyticsRequest($id, $startDate, $endDate, $order));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $row): BrandAnalyticsData => BrandAnalyticsData::from($row), $items ?? []);
    }

    /**
     * @return array<int, BrandData>
     */
    private function mapBrands(Response $response): array
    {
        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $brand): BrandData => BrandData::from($brand), $items ?? []);
    }
}
