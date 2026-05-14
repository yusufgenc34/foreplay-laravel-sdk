<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Boards;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/board/brands — offset-paginated brands tracked in a board.
 * Maps to OpenAPI op get_brands_by_board_id.
 */
final class GetBrandsByBoardIdRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $boardId,
        public readonly int $offset = 0,
        public readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/board/brands';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $q = [
            'board_id' => $this->boardId,
            'offset' => $this->offset,
        ];

        if ($this->limit !== null) {
            $q['limit'] = $this->limit;
        }

        return $q;
    }
}
