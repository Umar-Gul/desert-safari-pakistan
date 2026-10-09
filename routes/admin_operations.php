<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BookingPaymentController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

/*
| Phase 4C/4D routes. Loaded from routes/web.php (so the "web" group applies).
*/
Route::middleware(['auth', EnsureStaff::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Tours
        Route::get('tours', [TourController::class, 'index'])->name('tours.index');
        Route::get('tours/create', [TourController::class, 'create'])->name('tours.create');
        Route::post('tours', [TourController::class, 'store'])->name('tours.store');
        Route::get('tours/{tour}/edit', [TourController::class, 'edit'])->name('tours.edit');
        Route::put('tours/{tour}', [TourController::class, 'update'])->name('tours.update');
        Route::delete('tours/{tour}', [TourController::class, 'destroy'])->name('tours.destroy');
        Route::patch('tours/{tour}/toggle', [TourController::class, 'toggle'])->name('tours.toggle');

        // Bookings (static paths first, then {booking})
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::get('bookings/export', [BookingController::class, 'export'])->name('bookings.export');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::get('bookings/{booking}/voucher', [BookingController::class, 'voucher'])->name('bookings.voucher');
        Route::get('bookings/{booking}/voucher.pdf', \App\Http\Controllers\Admin\VoucherController::class)->name('bookings.voucher.pdf');
        Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
        Route::post('bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('bookings/{booking}/resend', [BookingController::class, 'resend'])->name('bookings.resend');
        Route::put('bookings/{booking}/notes', [BookingController::class, 'notes'])->name('bookings.notes');

        // Payments
        Route::post('bookings/{booking}/payments', [BookingPaymentController::class, 'store'])->name('bookings.payments.store');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::patch('payments/{payment}/complete', [BookingPaymentController::class, 'complete'])->name('payments.complete');
        Route::patch('payments/{payment}/refund', [BookingPaymentController::class, 'refund'])->name('payments.refund');

        // Availability calendar
        Route::get('availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
        Route::post('availabilities', [AvailabilityController::class, 'bulk'])->name('availabilities.bulk');

        // Moderation
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}/status', [ReviewController::class, 'status'])->name('reviews.status');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::patch('enquiries/{enquiry}/status', [EnquiryController::class, 'status'])->name('enquiries.status');
        Route::delete('enquiries/{enquiry}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

        // Staff users (super_admin only)
        Route::middleware('can:manage-users')->group(function () {
            Route::resource('users', UserController::class)->except('show');
        });
    });