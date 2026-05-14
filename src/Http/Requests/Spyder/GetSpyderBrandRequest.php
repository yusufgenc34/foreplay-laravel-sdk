<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Spyder;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/spyder/brand?brand_id=… — single Spyder-tracked brand. brand_id is query, not path.
 * Maps to OpenAPI op get_spyder_brand.
 */
final class GetSpyderBrandRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public readonly string $brandId) {}

    public function resolveEndpoint(): string
    {
        return '/spyder/brand';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return ['brand_id' => $this->brandId];
    }
}
