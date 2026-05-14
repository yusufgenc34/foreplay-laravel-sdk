<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Data;

use Spatie\LaravelData\Data;

/**
 * Shared response.metadata shape returned by every Foreplay endpoint.
 */
final class Metadata extends Data
{
    public function __construct(
        public readonly ?bool $success = null,
        public readonly ?string $message = null,
        public readonly ?int $status_code = null,
        public readonly ?int $processed_at = null,
        public readonly ?string $cursor = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $filters = null,
        public readonly ?string $order = null,
        public readonly ?int $count = null,
    ) {}
}
