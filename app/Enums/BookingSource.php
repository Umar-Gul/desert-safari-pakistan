<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum BookingSource: string
{
    use EnumHelpers;

    case Web = 'web';
    case Whatsapp = 'whatsapp';
    case Admin = 'admin';
}