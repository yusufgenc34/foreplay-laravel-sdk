<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Enums;

enum BrandSortOrder: string
{
    case MostRanked = 'most_ranked';
    case LeastRanked = 'least_ranked';
}
