<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Ads;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/ad/duplicates/{ad_id} — ads sharing the same image/video creative.
 * Maps to OpenAPI op get_group_duplicates_by_ad_id.
 */
final class GetAdDuplicatesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public readonly string $adId) {}

    public function resolveEndpoint(): string
    {
        return '/ad/duplicates/'.rawurlencode($this->adId);
    }
}
