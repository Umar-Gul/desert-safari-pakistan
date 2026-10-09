<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentTransactionStatus;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManualBookingRequest;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\CampType;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\Vehicle;
use App\Services\Admin\BookingListQuery;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    private const CUSTOMER_FIELDS = [
        'customer_name', 'email', 'phone', 'whatsapp', 'nationality',
        'pickup_time', 'pickup_location', 'pickup_address', 'city', 'special_requests',
    ];

    public function __construct(
        private readonly BookingService $bookings,
        private readonly BookingListQuery $list,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);

        return view('admin.bookings.index', [
            'bookings' => $this->list->build($request)->latest('id')->paginate(20)->withQueryString(),
            'tours' => Tour::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Booking::class);

        return view('admin.bookings.create', [
            'tours' => Tour::query()->active()->orderBy('title')->get(['id', 'title']),
            'campTypes' => CampType::query()->active()->ordered()->get(['id', 'name']),
            'vehicles' => Vehicle::query()->active()->ordered()->get(['id', 'name']),
            'addons' => Addon::query()->active()->ordered()->get(['id', 'name', 'price']),
            'cities' => (array) Setting::get('pickup_cities', []),
        ]);
    }

    public function store(ManualBookingRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $tour = Tour::query()->findOrFail($v['tour_id']);

        $booking = $this->bookings->create(
            $tour,
            $request->toQuoteInput(),
            Arr::only($v, self::CUSTOMER_FIELDS),
            $v['idempotency_key'],
            null,
            BookingSource::Admin,
        );

        if ($request->boolean('confirm_now') && $booking->status === BookingStatus::Pending) {
            $this->bookings->confirm($booking);
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', "Booking {$booking->reference} created.");
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['tour', 'campType', 'vehicle', 'coupon', 'addons', 'payments']);

        $cancellable = in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true);

        return view('admin.bookings.show', [
            'booking' => $booking,
            'quote' => $cancellable ? $this->bookings->cancellationQuote($booking) : null,
            'cancellable' => $cancellable,
            'canPay' => ! in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Refunded], true),
        ]);
    }

    public function voucher(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        return view('admin.bookings.voucher', ['booking' => $booking]);
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $this->bookings->confirm($booking);

        return back()->with('success', 'Booking confirmed.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $this->bookings->complete($booking);

        return back()->with('success', 'Booking marked as completed.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
            'waive_fee' => ['nullable', 'boolean'],
        ]);

        $quote = $this->bookings->cancel(
            $booking,
            'staff ('.$request->user()->name.')',
            $data['reason'] ?? null,
            $request->boolean('waive_fee'),
        );

        $message = $quote->isFree
            ? 'Booking cancelled. No cancellation fee.'
            : 'Booking cancelled. Cancellation fee: '.number_format($quote->feeAmount).'. Record any refund below.';

        return back()->with('success', $message);
    }

    public function resend(Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        match ($booking->status) {
            BookingStatus::Pending => BookingCreated::dispatch($booking),
            BookingStatus::Confirmed, BookingStatus::Completed => BookingConfirmed::dispatch($booking),
            default => throw new BookingException('Emails can only be resent for active bookings.'),
        };

        return back()->with('success', 'Notification event dispatched. Customer emails are connected in Phase 6.');
    }

    public function notes(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:5000']]);

        $booking->update(['admin_notes' => $data['admin_notes'] ?? null]);

        return back()->with('success', 'Notes saved.');
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', Booking::class);

        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);

        $query = $this->list->build($request)
            ->withSum(['payments as completed_total' => fn ($q) => $q->where('status', PaymentTransactionStatus::Completed->value)], 'amount')
            ->withSum(['payments as refunded_total' => fn ($q) => $q->where('status', PaymentTransactionStatus::Refunded->value)], 'amount');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads UTF-8

            fputcsv($out, [
                'Reference', 'Status', 'Payment status', 'Tour', 'Tour date', 'Pickup time', 'Customer', 'Email',
                'Phone', 'Nationality', 'City', 'Adults', 'Children', 'Subtotal', 'Add-ons', 'Discount', 'Tax',
                'Grand total', 'Paid', 'Currency', 'Source', 'Created',
            ]);

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $b) {
                    $paid = round((float) $b->completed_total - (float) $b->refunded_total, 2);

                    fputcsv($out, array_map([$this, 'safeCell'], [
                        $b->reference, $b->status->label(), $b->payment_status->label(), $b->tour?->title,
                        $b->tour_date->toDateString(), $b->pickup_time, $b->customer_name, $b->email,
                        $b->phone, $b->nationality, $b->city, $b->adults, $b->children, $b->subtotal,
                        $b->addons_total, $b->discount_total, $b->tax, $b->grand_total, $paid, $b->currency,
                        $b->source->label(), $b->created_at?->toDateTimeString(),
                    ]));
                }
            });

            fclose($out);
        }, 'bookings-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stops spreadsheet formula injection from customer-typed text. */
    public function safeCell(mixed $value): string
    {
        $text = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $text) === 1 ? "'".$text : $text;
    }
}