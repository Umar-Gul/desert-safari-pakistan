<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum EnquiryStatus: string
{
    use EnumHelpers;

    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
}