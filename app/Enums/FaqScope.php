<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum FaqScope: string
{
    use EnumHelpers;

    case Global = 'global';
    case Tour = 'tour';
}