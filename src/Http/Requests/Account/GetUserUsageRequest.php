<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Http\Requests\Account;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/usage — credit window + connected account for the authenticated user.
 * Maps to OpenAPI op get_user_usage.
 */
final class GetUserUsageRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/usage';
    }
}
