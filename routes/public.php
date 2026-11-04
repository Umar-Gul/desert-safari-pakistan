<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BookingTrackingController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\FeedbackController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\InfoPageController;
use App\Http\Controllers\Frontend\QuoteController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\TourController;
use Illuminate\Support\Facades\Route;

/*
| Public site. Loaded from routes/web.php (the "web" group applies).
| Static pages (about, terms, privacy, cookies, refund) stay in web.php.
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Tours, pricing and booking
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');
Route::post('/tours/{slug}/price', QuoteController::class)->middleware('throttle:quote')->name('tours.price');
Route::get('/tours/{slug}/checkout', [CheckoutController::class, 'show'])
    ->middleware(\App\Http\Middleware\PreserveGuestBooking::class)
    ->name('tours.checkout');
Route::post('/tours/{slug}/checkout', [CheckoutController::class, 'store'])
    ->middleware(['throttle:booking', \App\Http\Middleware\PreserveGuestBooking::class])
    ->name('tours.checkout.store');
Route::post('/tours/{slug}/reviews', [FeedbackController::class, 'review'])->middleware('throttle:forms')->name('reviews.store');

Route::get('/booking/{reference}/thank-you', [BookingTrackingController::class, 'thanks'])->middleware('signed')->name('booking.thanks');
Route::get('/track-booking', [BookingTrackingController::class, 'form'])->name('track.form');
Route::post('/track-booking', [BookingTrackingController::class, 'lookup'])->middleware('throttle:track')->name('track.lookup');
Route::post('/track-booking/cancel', [BookingTrackingController::class, 'cancel'])->middleware('throttle:track')->name('track.cancel');

// Catalogue pages
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/deserts/{slug}', [InfoPageController::class, 'desert'])->name('deserts.show');
Route::get('/camps/{slug}', [InfoPageController::class, 'camp'])->name('camps.show');
Route::get('/vehicles/{slug}', [InfoPageController::class, 'vehicle'])->name('vehicles.show');
Route::get('/facilities/{slug}', [InfoPageController::class, 'facility'])->name('facilities.show');
Route::get('/highlights/{slug}', [InfoPageController::class, 'highlight'])->name('highlights.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/search', SearchController::class)->name('search');

// Content
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// Contact and Q&A
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');
Route::post('/questions', [FeedbackController::class, 'question'])->middleware('throttle:forms')->name('questions.store');

// SEO and vouchers (Phase 6)
Route::get('/sitemap.xml', \App\Http\Controllers\Frontend\SitemapController::class)->name('sitemap');
Route::get('/robots.txt', \App\Http\Controllers\Frontend\RobotsController::class)->name('robots');
Route::get('/booking/{reference}/voucher.pdf', \App\Http\Controllers\Frontend\VoucherController::class)
    ->middleware('signed')->name('booking.voucher');
