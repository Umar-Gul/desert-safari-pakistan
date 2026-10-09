<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum GalleryItemType: string
{
    use EnumHelpers;

    case Image = 'image';
    case Video = 'video';
}