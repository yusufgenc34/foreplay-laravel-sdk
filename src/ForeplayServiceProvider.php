<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ForeplayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/foreplay.php', 'foreplay');

        $this->app->singleton(ForeplayClient::class, function (Application $app): ForeplayClient {
            /** @var array{api_key: ?string, base_url: string, timeout: int, retry: array{times: int, sleep_milliseconds: int, use_exponential_backoff: bool}, sandbox: bool} $config */
            $config = $app['config']->get('foreplay');

            return new ForeplayClient(
                apiKey: (string) ($config['api_key'] ?? ''),
                baseUrl: $config['base_url'],
                timeout: $config['timeout'],
                retryTimes: $config['retry']['times'],
                retrySleepMs: $config['retry']['sleep_milliseconds'],
                useExponentialBackoff: $config['retry']['use_exponential_backoff'],
                sandbox: $config['sandbox'] ?? false,
            );
        });

        $this->app->alias(ForeplayClient::class, 'foreplay');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/foreplay.php' => config_path('foreplay.php'),
            ], 'foreplay-config');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [ForeplayClient::class, 'foreplay'];
    }
}
