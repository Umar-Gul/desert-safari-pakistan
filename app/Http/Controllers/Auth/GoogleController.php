<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google sign-in failed: ' . $e->getMessage());

            return $this->fail('Google sign-in was cancelled or failed. Please try again.');
        }

        $email = $google->getEmail();
        $verified = (bool) ($google->user['email_verified'] ?? false);

        if (! $email || ! $verified) {
            return $this->fail('Your Google email address could not be verified.');
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $email)->first();

        if (! $user) {
            if (! config('services.google.allow_signup')) {
                return $this->fail('No account exists for this Google email. Please contact us.');
            }

            $user = User::forceCreate([
                'name'              => $google->getName() ?: Str::before($email, '@'),
                'email'             => $email,
                'password'          => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
        }

        $user->forceFill([
            'google_id'         => $google->getId(),
            'avatar'            => $google->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}