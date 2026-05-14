<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Pagination;

use Closure;
use Generator;

/**
 * Lazy cursor-based paginator. Yields every item across pages without
 * eagerly loading the full result set into memory.
 *
 * @template TItem
 */
final readonly class CursorPaginator
{
    /**
     * @param  Closure(?string $cursor): array{data: array<int, TItem>, cursor: ?string}  $fetchPage
     */
    public function __construct(private Closure $fetchPage) {}

    /**
     * @return Generator<int, TItem>
     */
    public function cursor(): Generator
    {
        $cursor = null;

        do {
            $page = ($this->fetchPage)($cursor);

            foreach ($page['data'] as $item) {
                yield $item;
            }

            $cursor = $page['cursor'] ?? null;
        } while (is_string($cursor) && $cursor !== '');
    }

    /**
     * Materialize all pages into a single array. Convenience helper —
     * prefer cursor() for large result sets.
     *
     * @return array<int, TItem>
     */
    public function collect(int $maxItems = PHP_INT_MAX): array
    {
        $items = [];

        foreach ($this->cursor() as $item) {
            $items[] = $item;

            if (count($items) >= $maxItems) {
                break;
            }
        }

        return $items;
    }
}
