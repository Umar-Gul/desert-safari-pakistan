<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnquiryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
        ];
    }
}
