<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Foreplay\LaravelSdk\Data\UsageData;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Account\GetUserUsageRequest;

final readonly class AccountResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * Current billing window + credit usage for the authenticated account.
     */
    public function usage(): UsageData
    {
        $response = $this->client->send(new GetUserUsageRequest);

        /** @var array<string, mixed>|null $data */
        $data = $response->json('data');

        return UsageData::from($data ?? []);
    }
}
