<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Spyder;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/spyder/brands — offset-paginated brands the user tracks in Spyder.
 * Maps to OpenAPI op get_spyder_brands.
 */
final class GetSpyderBrandsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly int $offset = 0,
        public readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/spyder/brands';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $q = ['offset' => $this->offset];

        if ($this->limit !== null) {
            $q['limit'] = $this->limit;
        }

        return $q;
    }
}
