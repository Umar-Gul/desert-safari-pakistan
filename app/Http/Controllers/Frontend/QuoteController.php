<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\PriceQuoteRequest;
use App\Models\Tour;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

/** Live price for the booking widget. Totals are always calculated here, never in the browser. */
class QuoteController extends Controller
{
    public function __invoke(
        PriceQuoteRequest $request,
        string $slug,
        PricingService $pricing,
        AvailabilityService $availability,
    ): JsonResponse {
        $tour = Tour::query()->active()->where('slug', $slug)->firstOrFail();
        $input = $request->toQuoteInput();

        $slot = $availability->check($tour, $input->tourDate, $input->guests());
        $price = $pricing->calculate($tour, $input);

        return response()->json([
            'price' => $price->toArray(),
            'remaining' => $slot->remaining,
        ]);
    }
}