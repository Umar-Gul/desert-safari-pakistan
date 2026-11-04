<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $pending = $request->session()->get('pending_booking');
        if (is_array($pending) && ! empty($pending['slug'])) {
            $params = array_intersect_key((array) ($pending['data'] ?? []), array_flip(['tour_date', 'adults', 'children', 'camp_type_id', 'vehicle_id', 'addons', 'coupon_code']));
            $checkout = route('tours.checkout', ['slug' => $pending['slug'], ...$params], absolute: false);
            $request->session()->put('url.intended', $checkout);

            return redirect()->intended($checkout)
                ->withInput((array) ($pending['data'] ?? []));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
