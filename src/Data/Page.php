<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

/**
 * Single-page cursor result. Use CursorPaginator for lazy iteration
 * across all pages; this is the per-request snapshot.
 *
 * @template TItem
 */
final readonly class Page
{
    /**
     * @param  array<int, TItem>  $items
     */
    public function __construct(
        public array $items,
        public ?string $cursor,
        public Metadata $metadata,
    ) {}

    public function hasMore(): bool
    {
        return is_string($this->cursor) && $this->cursor !== '';
    }

    /**
     * @return array<int, TItem>
     */
    public function items(): array
    {
        return $this->items;
    }
}
