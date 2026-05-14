<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Facades;

use Foreplay\LaravelSdk\ForeplayClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Foreplay\LaravelSdk\Http\Connector connector()
 * @method static \Saloon\Http\Response send(\Saloon\Http\Request $request)
 * @method static \Foreplay\LaravelSdk\Resources\AdResource ads()
 * @method static \Foreplay\LaravelSdk\Resources\BrandResource brands()
 * @method static \Foreplay\LaravelSdk\Resources\BoardResource boards()
 * @method static \Foreplay\LaravelSdk\Resources\SpyderResource spyder()
 * @method static \Foreplay\LaravelSdk\Resources\SwipefileResource swipefile()
 * @method static \Foreplay\LaravelSdk\Resources\AccountResource account()
 *
 * @see ForeplayClient
 */
final class Foreplay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ForeplayClient::class;
    }
}
