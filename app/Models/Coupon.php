<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdjustmentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => AdjustmentType::class,
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** Codes are always stored uppercase and trimmed. */
    protected function code(): Attribute
    {
        return Attribute::set(fn (string $value) => strtoupper(trim($value)));
    }

    /** Active, inside its date window and under its usage limit. Minimum amount is checked in PricingService. */
    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}