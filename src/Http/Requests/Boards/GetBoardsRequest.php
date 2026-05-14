<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Boards;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/boards — authenticated user's boards. Offset-paginated, limit ≤ 10.
 * Maps to OpenAPI op get_boards.
 */
final class GetBoardsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly int $offset = 0,
        public readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/boards';
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
