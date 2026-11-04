<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class PreserveGuestBooking
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            $slug = (string) $request->route('slug');
            $data = $request->only([
                'tour_date', 'adults', 'children', 'camp_type_id', 'vehicle_id', 'addons',
                'coupon_code', 'customer_name', 'email', 'phone', 'whatsapp', 'nationality',
                'pickup_time', 'pickup_location', 'pickup_address', 'city', 'special_requests',
                'terms', 'idempotency_key',
            ]);
            $request->session()->put('pending_bookings.'.$slug, $data);
            // Keep the active booking pointer for the existing checkout/auth flow.
            $request->session()->put('pending_booking', ['slug' => $slug, 'data' => $data]);

            $selection = Arr::only($data, [
                'tour_date', 'adults', 'children', 'camp_type_id', 'vehicle_id', 'addons', 'coupon_code',
            ]);
            $request->session()->put('url.intended', route('tours.checkout', ['slug' => $slug, ...$selection], absolute: false));
        }

        if (! $request->user()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
