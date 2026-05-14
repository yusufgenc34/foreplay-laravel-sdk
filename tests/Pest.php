<?php

declare(strict_types=1);

use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Tests\TestCase;
use Saloon\Http\Faking\MockClient;

uses(TestCase::class)->in(__DIR__);

function foreplayClient(MockClient $mock, int $retryTimes = 1): ForeplayClient
{
    $client = new ForeplayClient(
        apiKey: 'test-api-key',
        baseUrl: 'https://public.api.foreplay.co',
        timeout: 10,
        retryTimes: $retryTimes,
        retrySleepMs: 0,
        useExponentialBackoff: false,
    );

    $client->connector()->withMockClient($mock);

    return $client;
}
