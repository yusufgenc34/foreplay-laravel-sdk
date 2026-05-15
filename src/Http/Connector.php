<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http;

use Foreplay\LaravelSdk\Exceptions\EndpointNotFoundException;
use Foreplay\LaravelSdk\Exceptions\ForeplayException;
use Foreplay\LaravelSdk\Exceptions\InvalidApiKeyException;
use Foreplay\LaravelSdk\Exceptions\RateLimitExceededException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Connector as SaloonConnector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Throwable;

final class Connector extends SaloonConnector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
    ) {}

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/').'/api';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            // Foreplay expects the raw API key here — no "Bearer " prefix.
            'Authorization' => $this->apiKey,
            'User-Agent' => 'foreplay-laravel-sdk/0.1 (+saloonphp)',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        $stack = HandlerStack::create();

        // Foreplay expects repeated single-key params (languages=en&languages=fr).
        // PHP's http_build_query produces indexed brackets (languages[0]=en) which
        // Foreplay silently ignores. Strip the numeric index from every bracket pair.
        $stack->push(Middleware::mapRequest(static function (RequestInterface $request): RequestInterface {
            $uri = $request->getUri();
            $query = preg_replace('/%5B\d+%5D=/i', '=', $uri->getQuery()) ?? $uri->getQuery();

            return $request->withUri($uri->withQuery($query));
        }));

        return [
            'timeout' => $this->timeout,
            'connect_timeout' => max(5, (int) ceil($this->timeout / 3)),
            'handler' => $stack,
        ];
    }

    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        $status = $response->status();

        if ($status < 400) {
            return null;
        }

        $message = ForeplayException::messageFromResponse($response);

        return match (true) {
            $status === 401, $status === 403 => new InvalidApiKeyException($response, $message, $status, $senderException),
            $status === 404 => new EndpointNotFoundException($response, $message, $status, $senderException),
            $status === 429 => new RateLimitExceededException($response, $message, $status, $senderException),
            default => new ForeplayException($response, $message, $status, $senderException),
        };
    }
}
