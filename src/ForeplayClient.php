<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk;

use Foreplay\LaravelSdk\Http\Connector;
use Foreplay\LaravelSdk\Resources\AccountResource;
use Foreplay\LaravelSdk\Resources\AdResource;
use Foreplay\LaravelSdk\Resources\BoardResource;
use Foreplay\LaravelSdk\Resources\BrandResource;
use Foreplay\LaravelSdk\Resources\SpyderResource;
use Foreplay\LaravelSdk\Resources\SwipefileResource;
use Foreplay\LaravelSdk\Sandbox\Sandbox;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class ForeplayClient
{
    private readonly Connector $connector;

    private ?AdResource $ads = null;

    private ?BrandResource $brands = null;

    private ?BoardResource $boards = null;

    private ?SpyderResource $spyder = null;

    private ?SwipefileResource $swipefile = null;

    private ?AccountResource $account = null;

    public function __construct(
        string $apiKey,
        string $baseUrl,
        int $timeout = 30,
        private readonly int $retryTimes = 3,
        private readonly int $retrySleepMs = 250,
        private readonly bool $useExponentialBackoff = true,
        bool $sandbox = false,
        ?Sandbox $sandboxFixtures = null,
    ) {
        $this->connector = new Connector($apiKey, $baseUrl, $timeout);

        if ($sandbox) {
            ($sandboxFixtures ?? Sandbox::default())->installOn($this->connector);
        }
    }

    /**
     * Construct a client that replays pre-recorded fixtures instead of
     * hitting the live API. API key is irrelevant in sandbox mode.
     */
    public static function sandbox(?Sandbox $sandboxFixtures = null): self
    {
        return new self(
            apiKey: 'sandbox',
            baseUrl: 'https://public.api.foreplay.co',
            timeout: 10,
            retryTimes: 1,
            retrySleepMs: 0,
            useExponentialBackoff: false,
            sandbox: true,
            sandboxFixtures: $sandboxFixtures,
        );
    }

    public function connector(): Connector
    {
        return $this->connector;
    }

    public function ads(): AdResource
    {
        return $this->ads ??= new AdResource($this);
    }

    public function brands(): BrandResource
    {
        return $this->brands ??= new BrandResource($this);
    }

    public function boards(): BoardResource
    {
        return $this->boards ??= new BoardResource($this);
    }

    public function spyder(): SpyderResource
    {
        return $this->spyder ??= new SpyderResource($this);
    }

    public function swipefile(): SwipefileResource
    {
        return $this->swipefile ??= new SwipefileResource($this);
    }

    public function account(): AccountResource
    {
        return $this->account ??= new AccountResource($this);
    }

    /**
     * Send a request honoring the configured retry policy.
     * Exceptions thrown by getRequestException() (InvalidApiKey, NotFound, etc.)
     * are NOT retried because Saloon's default handleRetry returns false for them.
     */
    public function send(Request $request): Response
    {
        return $this->connector->sendAndRetry(
            request: $request,
            tries: max(1, $this->retryTimes),
            interval: $this->retrySleepMs,
            handleRetry: null,
            throw: true,
            useExponentialBackoff: $this->useExponentialBackoff,
        );
    }
}
