<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Exceptions;

use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class ForeplayException extends RequestException
{
    /**
     * Extract the human-readable error message from a Foreplay response body.
     * Foreplay wraps errors in metadata.message; fall back to common alternatives.
     */
    public static function messageFromResponse(Response $response): string
    {
        /** @var array<string, mixed>|null $body */
        $body = $response->json();

        if (is_array($body)) {
            $candidate = $body['metadata']['message']
                ?? $body['detail']
                ?? $body['message']
                ?? $body['error']
                ?? null;

            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        $fallback = $response->body();

        return $fallback !== '' ? $fallback : 'Foreplay API request failed.';
    }
}
