<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Brands;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Foreplay\LaravelSdk\Enums\Order;
use Foreplay\LaravelSdk\Support\QueryBuilder;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/brand/analytics — daily ad-distribution + creative velocity rows.
 * `id` accepts either a brand_id or a page_id. Max 30-day window.
 * Maps to OpenAPI op get_brands_analytics.
 */
final class GetBrandsAnalyticsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $id,
        public readonly string|CarbonInterface|DateTimeInterface|null $startDate = null,
        public readonly string|CarbonInterface|DateTimeInterface|null $endDate = null,
        public readonly ?Order $order = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/brand/analytics';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $q = ['id' => $this->id];

        if ($this->startDate !== null) {
            $q['start_date'] = QueryBuilder::date($this->startDate);
        }

        if ($this->endDate !== null) {
            $q['end_date'] = QueryBuilder::date($this->endDate);
        }

        if ($this->order !== null) {
            $q['order'] = $this->order->value;
        }

        return $q;
    }
}
