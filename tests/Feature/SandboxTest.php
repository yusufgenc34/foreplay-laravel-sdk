<?php

declare(strict_types=1);

use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Sandbox\Sandbox;

it('reports no missing fixtures for the currently-mapped operationIds', function () {
    expect(Sandbox::default()->missingFixtures())->toBe([]);
});

it('returns real Foreplay ad data through ForeplayClient::sandbox() without a network call', function () {
    $client = ForeplayClient::sandbox();

    $ads = iterator_to_array($client->ads()->search('nike')->cursor(), preserve_keys: false);

    expect($ads)->not->toBeEmpty()
        ->and($ads[0]->id)->toBeString()
        ->and($ads[0]->ad_id)->toBeString()
        ->and($ads[0]->brand_id)->toBeString();
});

it('returns the captured single-ad fixture from sandbox', function () {
    $client = ForeplayClient::sandbox();

    $ad = $client->ads()->get('any-id-ignored-by-sandbox');

    expect($ad->ad_id)->toBe('997846782598437')
        ->and($ad->id)->toBe('z53cwHaaUnov6A49WoTW');
});
