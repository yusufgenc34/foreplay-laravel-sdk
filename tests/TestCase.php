<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Tests;

use Foreplay\LaravelSdk\ForeplayServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends TestbenchTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            ForeplayServiceProvider::class,
        ];
    }
}
