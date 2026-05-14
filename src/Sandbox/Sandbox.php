<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Sandbox;

use Foreplay\LaravelSdk\Http\Connector;
use Foreplay\LaravelSdk\Http\Requests\Account\GetUserUsageRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdDuplicatesRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdsByBrandIdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdsByPageIdRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\SearchDiscoveryAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBoardAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBoardsRequest;
use Foreplay\LaravelSdk\Http\Requests\Boards\GetBrandsByBoardIdRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\DiscoverBrandsByAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\GetBrandsAnalyticsRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\GetBrandsByDomainRequest;
use Foreplay\LaravelSdk\Http\Requests\Brands\SearchDiscoveryBrandsRequest;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandAdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandRequest;
use Foreplay\LaravelSdk\Http\Requests\Spyder\GetSpyderBrandsRequest;
use Foreplay\LaravelSdk\Http\Requests\Swipefile\GetSwipefileAdsRequest;
use RuntimeException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * Loads pre-recorded JSON fixtures and installs them as a Saloon MockClient
 * onto a Connector. Lets users explore the SDK against real Foreplay payloads
 * without burning trial credits.
 *
 * Fixtures live in resources/fixtures/<operationId>.json and are captured by
 * the bin/capture-fixtures.sh script.
 */
final readonly class Sandbox
{
    /**
     * OpenAPI operationId → Saloon Request class. Add new entries here as
     * new resource groups are implemented.
     *
     * @var array<class-string, string>
     */
    private const REQUEST_TO_OPERATION = [
        GetAdRequest::class => 'get_ad_by_id',
        GetAdDuplicatesRequest::class => 'get_group_duplicates_by_ad_id',
        GetAdsByBrandIdsRequest::class => 'get_ads_by_brand_ids',
        GetAdsByPageIdRequest::class => 'get_brands_ads_by_page_id',
        SearchDiscoveryAdsRequest::class => 'search_discovery_ads',
        GetBrandsByDomainRequest::class => 'get_brands_by_domain',
        SearchDiscoveryBrandsRequest::class => 'search_discovery_brands',
        DiscoverBrandsByAdsRequest::class => 'discover_brands_by_ads',
        GetBrandsAnalyticsRequest::class => 'get_brands_analytics',
        GetBoardsRequest::class => 'get_boards',
        GetBoardAdsRequest::class => 'get_board_ads',
        GetBrandsByBoardIdRequest::class => 'get_brands_by_board_id',
        GetSpyderBrandsRequest::class => 'get_spyder_brands',
        GetSpyderBrandRequest::class => 'get_spyder_brand',
        GetSpyderBrandAdsRequest::class => 'get_spyder_brand_ads',
        GetSwipefileAdsRequest::class => 'get_swipefile_ads',
        GetUserUsageRequest::class => 'get_user_usage',
    ];

    public function __construct(public string $fixturesPath) {}

    public static function default(): self
    {
        return new self(dirname(__DIR__, 2).'/resources/fixtures');
    }

    public function installOn(Connector $connector): void
    {
        $connector->withMockClient($this->buildMockClient());
    }

    public function buildMockClient(): MockClient
    {
        $mocks = [];

        foreach (self::REQUEST_TO_OPERATION as $requestClass => $operationId) {
            $mocks[$requestClass] = $this->loadFixture($operationId);
        }

        return new MockClient($mocks);
    }

    /**
     * @return array<int, string> operationIds still missing a fixture file
     */
    public function missingFixtures(): array
    {
        $missing = [];

        foreach (self::REQUEST_TO_OPERATION as $operationId) {
            if (! is_file($this->pathFor($operationId))) {
                $missing[] = $operationId;
            }
        }

        return array_values(array_unique($missing));
    }

    private function loadFixture(string $operationId): MockResponse
    {
        $path = $this->pathFor($operationId);

        if (! is_file($path)) {
            throw new RuntimeException(sprintf(
                'Sandbox fixture missing for operation "%s" (expected %s). Run bin/capture-fixtures.sh against a live API key to regenerate.',
                $operationId,
                $path,
            ));
        }

        /** @var array<string, mixed> $body */
        $body = json_decode(
            (string) file_get_contents($path),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        // Saloon's class-keyed MockClient returns the same response for every
        // request of a given class. Stripping the cursor stops cursor-paginated
        // iterations from looping forever in sandbox mode — callers get one
        // realistic page of data and then terminate cleanly.
        if (isset($body['metadata']) && is_array($body['metadata'])) {
            $body['metadata']['cursor'] = null;
        }

        return MockResponse::make($body, 200);
    }

    private function pathFor(string $operationId): string
    {
        return $this->fixturesPath.DIRECTORY_SEPARATOR.$operationId.'.json';
    }
}
