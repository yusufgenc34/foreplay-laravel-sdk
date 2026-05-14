<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Exceptions;

final class RateLimitExceededException extends ForeplayException
{
    public function getRetryAfter(): ?int
    {
        $value = $this->getResponse()->header('Retry-After');

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
