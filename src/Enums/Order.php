<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Enums;

enum Order: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case LongestRunning = 'longest_running';
    case MostRelevant = 'most_relevant';
    case SavedNewest = 'saved_newest'; // swipefile-only
}
