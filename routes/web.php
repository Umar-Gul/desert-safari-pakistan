<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| Home becomes a controller in Phase 5. The pages below are STATIC Blade
| (no DB queries); header/footer values come from the settings composer.
*/

Route::view('/about', 'frontend.pages.static.about')->name('about');
Route::view('/terms-and-conditions', 'frontend.pages.static.terms')->name('terms');
Route::view('/privacy-policy', 'frontend.pages.static.privacy')->name('privacy');
Route::view('/cookie-policy', 'frontend.pages.static.cookies')->name('cookies');
Route::view('/cancellation-and-refund-policy', 'frontend.pages.static.refund')->name('refund');

/*
|--------------------------------------------------------------------------
| Staff entry point (Breeze redirects here after login)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return auth()->user()->isStaff()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('home');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Staff area (Phase 4).
require __DIR__.'/staff.php';

// Admin operations.
require __DIR__.'/admin_operations.php';

// Public site (Phase 5).
require __DIR__.'/public.php';



use App\Http\Controllers\Auth\SocialController;

Route::middleware('guest')->prefix('auth')->group(function () {
    Route::get('{provider}', [SocialController::class, 'redirect'])
        ->whereIn('provider', SocialController::PROVIDERS)
        ->name('social.redirect');

    Route::get('{provider}/callback', [SocialController::class, 'callback'])
        ->whereIn('provider', SocialController::PROVIDERS)
        ->middleware('throttle:10,1')
        ->name('social.callback');
});

