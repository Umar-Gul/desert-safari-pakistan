<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum BlogStatus: string
{
    use EnumHelpers;

    case Draft = 'draft';
    case Published = 'published';
}