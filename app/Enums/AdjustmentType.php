<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

/** Fixed amount or percentage. Used by camp types and coupons. */
enum AdjustmentType: string
{
    use EnumHelpers;

    case Fixed = 'fixed';
    case Percent = 'percent';
}