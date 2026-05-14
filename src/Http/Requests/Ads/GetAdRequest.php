<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Ads;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/ad/{adId} — single ad lookup.
 *
 * Maps to MCP tool get_ad_by_id (path variant). The query-string variant
 * get_ad_by_ad_id is functionally identical and intentionally not exposed.
 */
final class GetAdRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public readonly string $adId) {}

    public function resolveEndpoint(): string
    {
        return '/ad/'.rawurlencode($this->adId);
    }
}
