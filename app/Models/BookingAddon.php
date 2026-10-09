<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAddon extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->price_snapshot * (int) $this->quantity, 2));
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class)->withTrashed();
    }
}