<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tour_date' => 'date',
            'adults' => 'integer',
            'children' => 'integer',
            'subtotal' => 'decimal:2',
            'addons_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'source' => BookingSource::class,
            'cancelled_at' => 'datetime',
        ];
    }

    protected function guestsCount(): Attribute
    {
        return Attribute::get(fn () => (int) $this->adults + (int) $this->children);
    }

    /** Sum of completed payments. Uses the loaded relation when available. */
    public function paidAmount(): float
    {
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();

        return (float) $payments
            ->where('status', PaymentTransactionStatus::Completed)
            ->sum('amount');
    }

    public function balanceDue(): float
    {
        return max(0.0, round((float) $this->grand_total - $this->paidAmount(), 2));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('tour_date', '>=', today());
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campType(): BelongsTo
    {
        return $this->belongsTo(CampType::class)->withTrashed();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}