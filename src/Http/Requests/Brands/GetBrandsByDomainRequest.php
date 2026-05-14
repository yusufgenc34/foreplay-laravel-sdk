<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Brands;

use Foreplay\LaravelSdk\Enums\BrandSortOrder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/brand/getBrandsByDomain — single-page list of brands matching a domain.
 * Maps to OpenAPI op get_brands_by_domain.
 */
final class GetBrandsByDomainRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $domain,
        public readonly ?int $limit = null,
        public readonly ?BrandSortOrder $order = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/brand/getBrandsByDomain';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $q = ['domain' => $this->domain];

        if ($this->limit !== null) {
            $q['limit'] = $this->limit;
        }

        if ($this->order !== null) {
            $q['order'] = $this->order->value;
        }

        return $q;
    }
}
