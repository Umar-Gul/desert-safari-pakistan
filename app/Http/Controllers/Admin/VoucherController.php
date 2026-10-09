<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\VoucherService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VoucherController extends Controller
{
    public function __invoke(Booking $booking, VoucherService $vouchers): Response
    {
        Gate::authorize('view', $booking);

        return response($vouchers->pdf($booking), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$vouchers->filename($booking).'"',
        ]);
    }
}