<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\DataTransferObjects\QuoteInput;
use App\Enums\PaymentMethod;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\PriceQuoteRequest;
use App\Http\Requests\Frontend\StoreBookingRequest;
use App\Models\CampType;
use App\Models\Tour;
use App\Models\Vehicle;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
        private readonly BookingService $bookings,
    ) {}

    public function show(PriceQuoteRequest $request, string $slug): View|RedirectResponse
    {
        $tour = $this->tour($slug);
        $pending = $request->session()->get('pending_booking');
        $pendingData = (array) $request->session()->get('pending_bookings.'.$slug, []);
        if ($pendingData === [] && is_array($pending) && ($pending['slug'] ?? null) === $slug) {
            $pendingData = (array) ($pending['data'] ?? []);
        }
        $selection = $request->query->all();
        foreach (['tour_date', 'adults', 'children', 'camp_type_id', 'vehicle_id', 'addons', 'coupon_code'] as $key) {
            if (! array_key_exists($key, $selection) && array_key_exists($key, $pendingData)) {
                $selection[$key] = $pendingData[$key];
            }
        }
        $request->merge($selection);
        if ($pendingData !== []) {
            $request->session()->flash('_old_input', $pendingData);
        }
        $input = $request->toQuoteInput();

        try {
            $this->availability->check($tour, $input->tourDate, $input->guests());
            $price = $this->pricing->calculate($tour, $input);
        } catch (BookingException $e) {
            return redirect()
                ->route('tours.show', $tour->slug)
                ->withInput($request->query())
                ->withErrors(['booking' => $e->getMessage()]);
        }

        // A bad coupon must not block the booking: drop it and say so.
        $couponWarning = null;

        if ($price->couponError) {
            $couponWarning = $price->couponError;

            $input = new QuoteInput(
                tourDate: $input->tourDate,
                adults: $input->adults,
                children: $input->children,
                campTypeId: $input->campTypeId,
                vehicleId: $input->vehicleId,
                addons: $input->addons,
                couponCode: null,
            );

            $price = $this->pricing->calculate($tour, $input);
        }

        return view('frontend.checkout.show', [
            'tour' => $tour,
            'input' => $input,
            'price' => $price,
            'couponWarning' => $couponWarning,
            'campName' => $input->campTypeId ? CampType::query()->whereKey($input->campTypeId)->value('name') : null,
            'vehicleName' => $input->vehicleId ? Vehicle::query()->whereKey($input->vehicleId)->value('name') : null,
            'cities' => (array) setting('pickup_cities', []),
            'methods' => $this->paymentMethods(),
        ]);
    }

    public function store(StoreBookingRequest $request, string $slug): RedirectResponse
    {
        $tour = $this->tour($slug);

        // BookingException (full date, bad coupon...) renders as a form error on the checkout page.
        $booking = $this->bookings->create(
            $tour,
            $request->toQuoteInput(),
            $request->customerData(),
            $request->idempotencyKey(),
            $request->user()->id,
        );

        $request->session()->forget('pending_booking');
        $request->session()->forget('pending_bookings.'.$slug);
        $request->session()->forget('url.intended');

        return redirect(URL::temporarySignedRoute('booking.thanks', now()->addHours(6), ['reference' => $booking->reference]));
    }

    private function tour(string $slug): Tour
    {
        return Tour::query()->active()->where('slug', $slug)->with(['media', 'category:id,name,slug'])->firstOrFail();
    }

    /** @return list<string> */
    private function paymentMethods(): array
    {
        return collect((array) setting('payment_methods', []))
            ->map(fn ($value) => PaymentMethod::tryFrom((string) $value)?->label())
            ->filter()
            ->values()
            ->all();
    }
}
