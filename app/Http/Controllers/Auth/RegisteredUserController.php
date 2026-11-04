<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $pending = $request->session()->get('pending_booking');
        if (is_array($pending) && ! empty($pending['slug'])) {
            $params = array_intersect_key((array) ($pending['data'] ?? []), array_flip(['tour_date', 'adults', 'children', 'camp_type_id', 'vehicle_id', 'addons', 'coupon_code']));
            $checkout = route('tours.checkout', ['slug' => $pending['slug'], ...$params], absolute: false);
            $request->session()->put('url.intended', $checkout);

            return redirect()->intended($checkout)
                ->withInput((array) ($pending['data'] ?? []));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
