<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum QuickLinkGroup: string
{
    use EnumHelpers;

    case ActivityTypes = 'activity_types';
    case PopularSearches = 'popular_searches';
}