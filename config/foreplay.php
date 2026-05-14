<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    | Foreplay Data API key. Sent as the raw Authorization header value
    | (no "Bearer " prefix — Foreplay expects the key verbatim).
    */

    'api_key' => env('FOREPLAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | The SDK will append "/api" to this base when resolving requests.
    */

    'base_url' => env('FOREPLAY_BASE_URL', 'https://public.api.foreplay.co'),

    /*
    |--------------------------------------------------------------------------
    | Timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('FOREPLAY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry policy
    |--------------------------------------------------------------------------
    | Number of attempts and base sleep (ms) between retries. Exponential
    | backoff is enabled by default — sleep doubles after each attempt.
    */

    'retry' => [
        'times' => (int) env('FOREPLAY_RETRY_TIMES', 3),
        'sleep_milliseconds' => (int) env('FOREPLAY_RETRY_SLEEP_MS', 250),
        'use_exponential_backoff' => (bool) env('FOREPLAY_RETRY_EXPONENTIAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox mode
    |--------------------------------------------------------------------------
    | When true, all requests are answered by pre-recorded JSON fixtures
    | from resources/fixtures/ — no network call, no credits consumed.
    | Useful during local development and trial exploration.
    */

    'sandbox' => (bool) env('FOREPLAY_SANDBOX', false),
];
