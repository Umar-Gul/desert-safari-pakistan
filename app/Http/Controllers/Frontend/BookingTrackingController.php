<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\TrackBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingTrackingController extends Controller
{
    private const NOT_FOUND = 'We could not find a booking with those details. Please check the reference and the email or phone number you used when booking.';

    public function __construct(private readonly BookingService $bookings) {}

    /** Reached through a signed link right after booking. */
    public function thanks(string $reference): View
    {
        $booking = Booking::query()
            ->with(['tour', 'campType', 'vehicle', 'addons'])
            ->where('reference', $reference)
            ->firstOrFail();

        return view('frontend.checkout.thanks', [
            'booking' => $booking,
            'methods' => collect((array) setting('payment_methods', []))
                ->map(fn ($value) => PaymentMethod::tryFrom((string) $value)?->label())
                ->filter()->values()->all(),
        ]);
    }

    public function form(): View
    {
        return view('frontend.track.show', ['booking' => null, 'quote' => null, 'notice' => null]);
    }

    public function lookup(TrackBookingRequest $request): View|RedirectResponse
    {
        $booking = $this->bookings->lookup($request->input('reference'), $request->input('contact'));

        if (! $booking) {
            return back()->withInput()->withErrors(['booking' => self::NOT_FOUND]);
        }

        return $this->result($booking, $request->input('contact'));
    }

    public function cancel(TrackBookingRequest $request): View|RedirectResponse
    {
        $booking = $this->bookings->lookup($request->input('reference'), $request->input('contact'));

        if (! $booking) {
            return back()->withInput()->withErrors(['booking' => self::NOT_FOUND]);
        }

        $quote = $this->bookings->cancel($booking, 'customer');

        $notice = $quote->isFree
            ? 'Your booking has been cancelled. No cancellation fee applies.'
            : 'Your booking has been cancelled. A cancellation fee of '.money($quote->feeAmount).' applies; our team will contact you about any refund.';

        return $this->result($booking->fresh(['tour', 'addons', 'payments']), $request->input('contact'), $notice);
    }

    private function result(Booking $booking, string $contact, ?string $notice = null): View
    {
        $booking->load(['campType', 'vehicle']);

        $cancellable = in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true);

        return view('frontend.track.show', [
            'booking' => $booking,
            'contact' => $contact,
            'quote' => $cancellable ? $this->bookings->cancellationQuote($booking) : null,
            'notice' => $notice,
        ]);
    }
}