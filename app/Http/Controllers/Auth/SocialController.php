<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialController extends Controller
{
    public const PROVIDERS = ['google', 'github', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureEnabled($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->ensureEnabled($provider);
        $label = ucfirst($provider);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            Log::warning("{$label} sign-in failed: " . $e->getMessage());

            return $this->fail("{$label} sign-in was cancelled or failed. Please try again.");
        }

        $verified = $this->emailVerified($provider, $social);

        $account = SocialAccount::with('user')
            ->where('provider', $provider)
            ->where('provider_id', (string) $social->getId())
            ->first();

        $user = $account?->user;

        if (! $user) {
            $email = $social->getEmail();

            if (! $email) {
                return $this->fail("{$label} did not share an email address with us. Please make your email available and try again.");
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                // Never attach a social login to an existing account unless the provider vouches for the email.
                if (! $verified) {
                    return $this->fail("An account with this email already exists. Please sign in with your password.");
                }
            } else {
                if (! config('services.social.allow_signup')) {
                    return $this->fail('No account exists for this email. Please contact us.');
                }

                $user = User::forceCreate([
                    'name'     => $social->getName() ?: $social->getNickname() ?: Str::before($email, '@'),
                    'email'    => $email,
                    'password' => Hash::make(Str::random(40)),
                ]);
            }

            $user->socialAccounts()->updateOrCreate(
                ['provider' => $provider],
                ['provider_id' => (string) $social->getId()],
            );
        }

        $user->forceFill([
            'avatar'            => $user->avatar ?: $social->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? ($verified ? now() : null),
        ])->save();

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Only providers that actually confirm the email count as verified.
     * Facebook does not expose this, so it is treated as unverified.
     */
    private function emailVerified(string $provider, SocialUser $social): bool
    {
        return match ($provider) {
            'google' => (bool) ($social->user['email_verified'] ?? false),
            // Socialite only returns the primary, verified GitHub email.
            'github' => filled($social->getEmail()),
            default  => false,
        };
    }

    private function ensureEnabled(string $provider): void
    {
        abort_unless(
            in_array($provider, self::PROVIDERS, true) && config("services.{$provider}.client_id"),
            404
        );
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}