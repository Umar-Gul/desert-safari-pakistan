<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BookingPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    /** Record money received, or create a pending payment (e.g. waiting for a bank transfer). */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);

        $data = $request->validate([
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'received' => ['nullable', 'boolean'],
            'transaction_id' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $method = PaymentMethod::from($data['method']);
        $amount = (float) $data['amount'];

        if ($request->boolean('received')) {
            $this->payments->recordManual($booking, $method, $amount, $data['transaction_id'] ?? null, $data['notes'] ?? null);
            $message = 'Payment recorded.';
        } else {
            $this->payments->initiate($booking, $method, $amount);
            $message = 'Pending payment created.';
        }

        return back()->with('success', $message);
    }

    /** A pending payment was received. */
    public function complete(Request $request, Payment $payment): RedirectResponse
    {
        $payment->load('booking');

        Gate::authorize('update', $payment->booking);

        $data = $request->validate(['transaction_id' => ['nullable', 'string', 'max:120']]);

        $this->payments->markCompleted($payment, $data['transaction_id'] ?? null);

        return back()->with('success', 'Payment marked as received.');
    }

    /** Refunds are admin-only (FinancePolicy). */
    public function refund(Payment $payment): RedirectResponse
    {
        Gate::authorize('update', $payment);

        $payment->load('booking');

        $this->payments->markRefunded($payment);

        return back()->with('success', 'Payment marked as refunded.');
    }
}