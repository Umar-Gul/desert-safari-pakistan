<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourHighlight extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}