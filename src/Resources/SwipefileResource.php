<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Resources;

use Foreplay\LaravelSdk\Data\AdData;
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\ForeplayClient;
use Foreplay\LaravelSdk\Http\Requests\Swipefile\GetSwipefileAdsRequest;

final readonly class SwipefileResource
{
    public function __construct(private ForeplayClient $client) {}

    /**
     * Ads saved in the authenticated user's personal swipefile. Offset-paginated.
     * Use filters->order = Order::SavedNewest for chronological save-date sort.
     *
     * @return array<int, AdData>
     */
    public function ads(?AdFiltersData $filters = null, int $offset = 0): array
    {
        $response = $this->client->send(new GetSwipefileAdsRequest(
            filters: $filters ?? new AdFiltersData,
            offset: $offset,
        ));

        /** @var array<int, array<string, mixed>>|null $items */
        $items = $response->json('data');

        return array_map(static fn (array $a): AdData => AdData::from($a), $items ?? []);
    }
}
