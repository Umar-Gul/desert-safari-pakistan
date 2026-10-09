<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Shared by the bookings list and the CSV export so both always agree. */
final class BookingListQuery
{
    /** @return Builder<Booking> */
    public function build(Request $request): Builder
    {
        $status = BookingStatus::tryFrom((string) $request->input('status'));
        $payment = PaymentStatus::tryFrom((string) $request->input('payment_status'));
        $term = trim((string) $request->input('q'));

        return Booking::query()
            ->with('tour:id,title')
            ->when($term !== '', function (Builder $q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $q->where(fn (Builder $w) => $w
                    ->where('reference', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->when($status, fn (Builder $q) => $q->where('status', $status->value))
            ->when($payment, fn (Builder $q) => $q->where('payment_status', $payment->value))
            ->when($request->filled('tour_id'), fn (Builder $q) => $q->where('tour_id', (int) $request->input('tour_id')))
            ->when($request->filled('from'), fn (Builder $q) => $q->whereDate('tour_date', '>=', (string) $request->input('from')))
            ->when($request->filled('to'), fn (Builder $q) => $q->whereDate('tour_date', '<=', (string) $request->input('to')));
    }
}