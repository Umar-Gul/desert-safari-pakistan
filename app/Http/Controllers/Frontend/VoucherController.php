<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\VoucherService;
use Illuminate\Http\Response;

/** Customer download. Only reachable through a signed link shown after a verified lookup. */
class VoucherController extends Controller
{
    public function __invoke(string $reference, VoucherService $vouchers): Response
    {
        $booking = Booking::query()
            ->where('reference', $reference)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
            ->firstOrFail();

        return response($vouchers->pdf($booking), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$vouchers->filename($booking).'"',
        ]);
    }
}