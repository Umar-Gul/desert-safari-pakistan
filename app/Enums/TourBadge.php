<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum TourBadge: string
{
    use EnumHelpers;

    case TopRated = 'top_rated';
    case BestSelling = 'best_selling';
    case Special = 'special';
}