<?php

declare(strict_types=1);

use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Enums\BrandSortOrder;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Sandbox\Sandbox;

it('has fixtures wired for every mapped operationId', function () {
    expect(Sandbox::default()->missingFixtures())->toBe([]);
});

it('returns account usage with credit window', function () {
    $usage = ForeplayClient::sandbox()->account()->usage();

    expect($usage->total_credits)->toBe(10000)
        ->and($usage->remaining_credits)->toBeInt()
        ->and($usage->user)->not->toBeNull()
        ->and($usage->user?->email)->toBe('you@example.com');
});

it('returns brands by domain', function () {
    $brands = ForeplayClient::sandbox()->brands()->byDomain('nike.com', limit: 3, order: BrandSortOrder::MostRanked);

    expect($brands)->not->toBeEmpty()
        ->and($brands[0]->id)->toBeString()
        ->and($brands[0]->name)->toBeString();
});

it('searches brands by fuzzy name', function () {
    $brands = ForeplayClient::sandbox()->brands()->search('nike', limit: 5);

    expect($brands)->not->toBeEmpty()
        ->and($brands[0]->name)->toBeString();
});

it('discovers brands by ad filters', function () {
    $brands = ForeplayClient::sandbox()->brands()->discoverByAds(
        new AdFiltersData(live: true, limit: 5)
    );

    expect($brands)->not->toBeEmpty();
});

it('returns brand analytics rows', function () {
    $rows = ForeplayClient::sandbox()->brands()->analytics('s5N67F6UaV5gScE5SN03');

    expect($rows)->not->toBeEmpty()
        ->and($rows[0]->date)->toBeString()
        ->and($rows[0]->active_count)->toBeInt();
});

it('lists the user boards (empty in trial fixture)', function () {
    $boards = ForeplayClient::sandbox()->boards()->all();

    expect($boards)->toBeArray();
});

it('lists board ads via cursor paginator', function () {
    $ads = iterator_to_array(
        ForeplayClient::sandbox()->boards()->ads('sandbox_board_1')->cursor(),
        preserve_keys: false,
    );

    expect($ads)->not->toBeEmpty()
        ->and($ads[0]->ad_id)->toBeString();
});

it('lists brands tracked inside a board', function () {
    $brands = ForeplayClient::sandbox()->boards()->brands('sandbox_board_1');

    expect($brands)->toBeArray();
});

it('lists spyder-tracked brands', function () {
    $brands = ForeplayClient::sandbox()->spyder()->brands();

    expect($brands)->toBeArray();
});

it('returns a single spyder brand', function () {
    $brand = ForeplayClient::sandbox()->spyder()->brand('sandbox_spyder_brand_1');

    expect($brand->id)->toBeString();
});

it('iterates spyder brand ads via cursor', function () {
    $ads = iterator_to_array(
        ForeplayClient::sandbox()->spyder()->ads('sandbox_spyder_brand_1')->cursor(),
        preserve_keys: false,
    );

    expect($ads)->not->toBeEmpty();
});

it('returns swipefile ads with offset + filters', function () {
    $ads = ForeplayClient::sandbox()->swipefile()->ads(
        filters: new AdFiltersData(order: Order::SavedNewest, limit: 3),
        offset: 0,
    );

    expect($ads)->toBeArray();
});
