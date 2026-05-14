<?php

declare(strict_types=1);

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Enums\DisplayFormat;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\Exceptions\EndpointNotFoundException;
use Foreplay\LaravelSdk\Exceptions\InvalidApiKeyException;
use Foreplay\LaravelSdk\Exceptions\RateLimitExceededException;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\GetAdsByBrandIdsRequest;
use Foreplay\LaravelSdk\Http\Requests\Ads\SearchDiscoveryAdsRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

it('returns a typed AdData for a single ad', function () {
    $mock = new MockClient([
        MockResponse::make([
            'data' => [
                'id' => 'ad_1',
                'ad_id' => 'ad_1',
                'name' => 'Summer Sale',
                'brand_id' => 'brand_123',
                'live' => true,
                'display_format' => 'video',
                'publisher_platform' => ['facebook', 'instagram'],
                'running_duration' => ['days' => 12],
            ],
            'metadata' => ['success' => true, 'status_code' => 200],
        ], 200),
    ]);

    $ad = foreplayClient($mock)->ads()->get('ad_1');

    expect($ad->id)->toBe('ad_1')
        ->and($ad->name)->toBe('Summer Sale')
        ->and($ad->live)->toBeTrue()
        ->and($ad->publisher_platform)->toBe(['facebook', 'instagram'])
        ->and($ad->running_duration?->days)->toBe(12);
});

it('uses the path variant for single-ad lookups', function () {
    $mock = new MockClient([
        GetAdRequest::class => MockResponse::make([
            'data' => ['id' => 'ad_42'],
            'metadata' => [],
        ], 200),
    ]);

    foreplayClient($mock)->ads()->get('ad_42');

    $mock->assertSent(function (Request $req, Response $resp): bool {
        return str_ends_with($resp->getPendingRequest()->getUrl(), '/api/ad/ad_42');
    });
});

it('maps 404 responses to EndpointNotFoundException', function () {
    $mock = new MockClient([
        MockResponse::make(['detail' => 'Ad not found'], 404),
    ]);

    foreplayClient($mock)->ads()->get('missing');
})->throws(EndpointNotFoundException::class, 'Ad not found');

it('maps 401 responses to InvalidApiKeyException', function () {
    $mock = new MockClient([
        MockResponse::make(['metadata' => ['message' => 'Invalid API key']], 401),
    ]);

    foreplayClient($mock)->ads()->get('ad_1');
})->throws(InvalidApiKeyException::class, 'Invalid API key');

it('maps 429 responses to RateLimitExceededException with Retry-After', function () {
    $mock = new MockClient([
        MockResponse::make(
            body: ['metadata' => ['message' => 'rate limited']],
            status: 429,
            headers: ['Retry-After' => '60'],
        ),
    ]);

    try {
        foreplayClient($mock)->ads()->get('ad_1');
        test()->fail('Expected RateLimitExceededException');
    } catch (RateLimitExceededException $e) {
        expect($e->getRetryAfter())->toBe(60);
    }
});

it('lazily paginates across pages until cursor is null', function () {
    $mock = new MockClient([
        MockResponse::make([
            'data' => [
                ['id' => 'ad_1', 'name' => 'one'],
                ['id' => 'ad_2', 'name' => 'two'],
            ],
            'metadata' => ['cursor' => 'CUR_PAGE_2'],
        ], 200),
        MockResponse::make([
            'data' => [
                ['id' => 'ad_3', 'name' => 'three'],
            ],
            'metadata' => ['cursor' => null],
        ], 200),
    ]);

    $client = foreplayClient($mock);

    $ads = iterator_to_array(
        $client->ads()->search(query: 'sale')->cursor(),
        preserve_keys: false,
    );

    expect($ads)->toHaveCount(3)
        ->and($ads[0]->id)->toBe('ad_1')
        ->and($ads[2]->id)->toBe('ad_3');

    $mock->assertSentCount(2);
});

it('forwards the cursor on subsequent paginated requests', function () {
    $mock = new MockClient([
        MockResponse::make([
            'data' => [['id' => 'ad_1']],
            'metadata' => ['cursor' => 'NEXT'],
        ], 200),
        MockResponse::make([
            'data' => [['id' => 'ad_2']],
            'metadata' => ['cursor' => null],
        ], 200),
    ]);

    $client = foreplayClient($mock);
    iterator_to_array($client->ads()->search()->cursor());

    $recorded = $mock->getRecordedResponses();
    $secondPending = $recorded[1]->getPendingRequest();

    expect($secondPending->query()->get('cursor'))->toBe('NEXT');
});

it('serializes filters via QueryBuilder (enums, bool, date)', function () {
    $mock = new MockClient([
        SearchDiscoveryAdsRequest::class => MockResponse::make([
            'data' => [],
            'metadata' => ['cursor' => null],
        ], 200),
    ]);

    $filters = new AdFiltersData(
        live: true,
        display_format: [DisplayFormat::Video, DisplayFormat::Carousel],
        start_date: '2025-01-01',
        order: Order::LongestRunning,
        limit: 50,
    );

    iterator_to_array(
        foreplayClient($mock)->ads()->search('sale', $filters)->cursor()
    );

    $mock->assertSent(function (Request $req, Response $resp): bool {
        $q = $resp->getPendingRequest()->query();

        return $q->get('live') === 'true'
            && $q->get('display_format') === ['video', 'carousel']
            && $q->get('start_date') === '2025-01-01 00:00:00'
            && $q->get('order') === 'longest_running'
            && $q->get('limit') === 50
            && $q->get('query') === 'sale';
    });
});

it('targets the brand-ids endpoint for byBrandIds()', function () {
    $mock = new MockClient([
        GetAdsByBrandIdsRequest::class => MockResponse::make([
            'data' => [],
            'metadata' => ['cursor' => null],
        ], 200),
    ]);

    iterator_to_array(
        foreplayClient($mock)->ads()->byBrandIds(['brand_a', 'brand_b'])->cursor()
    );

    $mock->assertSent(function (Request $req, Response $resp): bool {
        $pending = $resp->getPendingRequest();

        return str_ends_with($pending->getUrl(), '/api/brand/getAdsByBrandId')
            && $pending->query()->get('brand_ids') === ['brand_a', 'brand_b'];
    });
});
