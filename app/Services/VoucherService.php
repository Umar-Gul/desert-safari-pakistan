<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class VoucherService
{
    public function pdf(Booking $booking): string
    {
        $booking->loadMissing(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        return Pdf::loadView('pdf.voucher', ['booking' => $booking])->setPaper('a4')->output();
    }

    public function filename(Booking $booking): string
    {
        return 'voucher-'.$booking->reference.'.pdf';
    }
}