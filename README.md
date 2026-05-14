# Foreplay Laravel SDK

Unofficial Laravel SDK for the [Foreplay](https://foreplay.co) Data API
(`public.api.foreplay.co`). Wraps every read-only GET endpoint with typed
responses, exception mapping, cursor-based pagination helpers, and a
sandbox mode that replays pre-recorded fixtures so you can explore the
surface without spending credits.

> This is a third-party, community-maintained SDK. It is not affiliated
> with, endorsed by, or supported by Foreplay Inc.

## Requirements

- PHP 8.3 or newer
- Laravel 13.x
- A Foreplay Data API key (generated from the Foreplay dashboard)

## Installation

```bash
composer require yusufgenc/foreplay-laravel-sdk
```

The service provider and the `Foreplay` facade are auto-discovered. Publish
the config if you need to override the defaults:

```bash
php artisan vendor:publish --tag=foreplay-config
```

## Configuration

Add your API key to `.env`. The Foreplay API accepts the raw key as the
`Authorization` header (no `Bearer` prefix), which the SDK handles for you.

```dotenv
FOREPLAY_API_KEY=your_key_here
```

Optional settings (defaults shown):

```dotenv
FOREPLAY_BASE_URL=https://public.api.foreplay.co
FOREPLAY_TIMEOUT=30
FOREPLAY_RETRY_TIMES=3
FOREPLAY_RETRY_SLEEP_MS=250
FOREPLAY_RETRY_EXPONENTIAL=true
FOREPLAY_SANDBOX=false
```

Retries are applied to network errors and 5xx responses with exponential
backoff. Client errors (4xx) are not retried — they are translated into
exceptions immediately.

## Quick start

```php
use Foreplay\LaravelSdk\Facades\Foreplay;

$ad = Foreplay::ads()->get('997846782598437');

echo $ad->name;       // "Stadium"
echo $ad->brand_id;   // "s5N67F6UaV5gScE5SN03"
```

The same client is available from the container:

```php
$client = app(\Foreplay\LaravelSdk\ForeplayClient::class);
$ad = $client->ads()->get('997846782598437');
```

## Resources

The client exposes one resource per logical group. Methods return either a
typed DTO, an array of DTOs, or a `CursorPaginator` for cursor-paginated
endpoints.

### Ads

```php
$ad     = Foreplay::ads()->get($adId);                       // AdData
$dupes  = Foreplay::ads()->duplicates($adId);                // AdData[]
$search = Foreplay::ads()->search('nike', $filters);         // CursorPaginator
$brand  = Foreplay::ads()->byBrandIds([$brandId], $filters); // CursorPaginator
$page   = Foreplay::ads()->byPageId($pageId, $filters);      // CursorPaginator
```

### Brands

```php
$byDomain = Foreplay::brands()->byDomain('nike.com');
$results  = Foreplay::brands()->search('nike');
$discover = Foreplay::brands()->discoverByAds($filters);
$rows     = Foreplay::brands()->analytics($brandIdOrPageId, $start, $end);
```

`analytics()` returns one row per day; the API enforces a 30-day maximum
window per call.

### Boards

```php
$boards = Foreplay::boards()->all($offset = 0, $limit = 10);
$ads    = Foreplay::boards()->ads($boardId, $filters);  // CursorPaginator
$brands = Foreplay::boards()->brands($boardId, $offset = 0, $limit = 10);
```

### Spyder

```php
$tracked = Foreplay::spyder()->brands($offset = 0, $limit = 10);
$brand   = Foreplay::spyder()->brand($brandId);
$ads     = Foreplay::spyder()->ads($brandId, $filters);  // CursorPaginator
```

### Swipefile

```php
$saved = Foreplay::swipefile()->ads($filters, $offset = 0);
```

### Account

```php
$usage = Foreplay::account()->usage();
echo $usage->remaining_credits;
echo $usage->user?->email;
```

## Pagination

Cursor-paginated endpoints return a `CursorPaginator` that exposes a lazy
`Generator`. Pages are fetched on demand, so iteration is memory-safe even
across large result sets.

```php
foreach (Foreplay::ads()->search('shoes')->cursor() as $ad) {
    // yields every ad across every page
}
```

For bounded results, use `collect($max)`:

```php
$first50 = Foreplay::ads()->search('shoes')->collect(50);
```

Endpoints that use offset pagination (`boards.all`, `boards.brands`,
`spyder.brands`, `swipefile.ads`) take explicit `$offset` and `$limit`
arguments and return a single page per call. The Foreplay API caps `limit`
at 10 on those endpoints.

## Filtering

`AdFiltersData` is the shared filter shape used by every ad-listing
endpoint. Dates accept `string`, `DateTimeInterface`, or Carbon and are
normalised to `Y-m-d H:i:s` in UTC before being sent. Enum arguments are
strictly typed.

```php
use Foreplay\LaravelSdk\Data\AdFiltersData;
use Foreplay\LaravelSdk\Enums\DisplayFormat;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\Enums\PublisherPlatform;

$filters = new AdFiltersData(
    live: true,
    display_format: [DisplayFormat::Video, DisplayFormat::Carousel],
    publisher_platform: [PublisherPlatform::Facebook, PublisherPlatform::Instagram],
    start_date: '2025-01-01',
    end_date: '2025-12-31',
    running_duration_min_days: 30,
    order: Order::LongestRunning,
    limit: 50,
);

$ads = Foreplay::ads()->search('skincare', $filters);
```

All filter fields are nullable; pass only what you care about.

## Exception handling

Every HTTP failure raises a typed exception that extends
`Foreplay\LaravelSdk\Exceptions\ForeplayException`.

| Status        | Exception                       |
|---------------|---------------------------------|
| 401 / 403     | `InvalidApiKeyException`        |
| 404           | `EndpointNotFoundException`     |
| 429           | `RateLimitExceededException`    |
| Other 4xx/5xx | `ForeplayException`             |

```php
use Foreplay\LaravelSdk\Exceptions\RateLimitExceededException;

try {
    Foreplay::ads()->search('nike');
} catch (RateLimitExceededException $e) {
    sleep($e->getRetryAfter() ?? 60);
}
```

The original Saloon `Response` is available via `$exception->getResponse()`
if you need to inspect headers or the raw body.

## Sandbox mode

Sandbox mode answers every request from a pre-recorded JSON fixture
shipped with the package. No network call is made and no credits are
consumed. Useful during local development, demo recordings, and trial
exploration.

Enable it via configuration:

```dotenv
FOREPLAY_SANDBOX=true
```

Or instantiate the client directly:

```php
use Foreplay\LaravelSdk\ForeplayClient;

$client = ForeplayClient::sandbox();

$ad     = $client->ads()->get('any-id');
$search = $client->ads()->search('nike');
$usage  = $client->account()->usage();
```

The shipped fixtures cover all 17 endpoints. The dataset combines
real responses captured against a live account (ads, brands, analytics,
usage) with composed playground data for features the public API does not
expose for writes (boards, Spyder tracking, swipefile saves).

## Testing

The package includes Pest tests that exercise the connector against
Saloon's `MockClient`, covering happy paths, exception mapping, cursor
pagination, and filter serialisation.

```bash
composer install
./vendor/bin/pest
```

## Regenerating fixtures

If you have your own Foreplay API key and want to refresh the sandbox
dataset with your account state:

```bash
FOREPLAY_API_KEY=your_key bash bin/capture-fixtures.sh
python3 bin/build-playground-fixtures.py
```

`capture-fixtures.sh` overwrites fixtures with live responses (PII in
`/api/usage` is scrubbed automatically). `build-playground-fixtures.py`
composes realistic fixtures from the real captures for any endpoint that
came back empty (boards, Spyder, swipefile).

## Versioning

This package follows semantic versioning. Until the upstream API and
this SDK reach `1.0`, minor releases may contain breaking changes; pin
with a tilde range (for example `~0.1`) if stability is important.

## Issues

Open issues and feature requests at
[github.com/yusufgenc34/foreplay-laravel-sdk/issues](https://github.com/yusufgenc34/foreplay-laravel-sdk/issues).

## Credits

- [Saloon](https://github.com/saloonphp/saloon) — HTTP toolkit
- [spatie/laravel-data](https://github.com/spatie/laravel-data) — DTOs
- [Foreplay Inc.](https://foreplay.co) for the underlying data product

## License

MIT. See [LICENSE](LICENSE).
