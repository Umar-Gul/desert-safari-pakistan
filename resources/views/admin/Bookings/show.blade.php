@php
    $txColors = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'stone'];
    $money = fn ($n) => $booking->currency.' '.number_format((float) $n);
    $paid = $booking->paidAmount();
    $balance = $booking->balanceDue();
    $in = 'block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500';
@endphp

<x-admin.layout :title="$booking->reference">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold text-stone-900">{{ $booking->reference }}</h1>
            <x-admin.badge :color="$booking->status->color()" :label="$booking->status->label()" />
            <x-admin.badge :color="$booking->payment_status->color()" :label="$booking->payment_status->label()" />
            <span class="text-xs text-stone-500">via {{ $booking->source->label() }}, {{ $booking->created_at?->format('d M Y H:i') }}</span>
        </div>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('admin.bookings.voucher', $booking) }}" target="_blank" class="rounded-md border border-stone-300 bg-white px-3 py-1.5 hover:bg-stone-50">Print voucher</a>
            <a href="{{ route('admin.bookings.voucher.pdf', $booking) }}" class="rounded-md border border-stone-300 bg-white px-3 py-1.5 hover:bg-stone-50">Download PDF</a>
            <a href="{{ route('admin.bookings.index') }}" class="rounded-md border border-stone-300 bg-white px-3 py-1.5 hover:bg-stone-50">All bookings</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-stone-900">Trip</h2>
                <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-stone-500">Tour</dt><dd class="font-medium">{{ $booking->tour?->title }}</dd></div>
                    <div><dt class="text-stone-500">Date</dt><dd class="font-medium">{{ $booking->tour_date->format('l, d M Y') }}{{ $booking->pickup_time ? ' at '.$booking->pickup_time : '' }}</dd></div>
                    <div><dt class="text-stone-500">Guests</dt><dd class="font-medium">{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</dd></div>
                    <div><dt class="text-stone-500">Camp / vehicle</dt><dd class="font-medium">{{ $booking->campType?->name ?? 'None' }} / {{ $booking->vehicle?->name ?? 'None' }}</dd></div>
                    <div><dt class="text-stone-500">Pickup</dt><dd class="font-medium">{{ $booking->pickup_location ?: '-' }}{{ $booking->city ? ', '.$booking->city : '' }}</dd></div>
                    <div><dt class="text-stone-500">Pickup address</dt><dd class="font-medium">{{ $booking->pickup_address ?: '-' }}</dd></div>
                    @if ($booking->special_requests)
                        <div class="sm:col-span-2"><dt class="text-stone-500">Special requests</dt><dd class="whitespace-pre-line">{{ $booking->special_requests }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-stone-900">Customer</h2>
                <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-stone-500">Name</dt><dd class="font-medium">{{ $booking->customer_name }}</dd></div>
                    <div><dt class="text-stone-500">Email</dt><dd class="font-medium"><a href="mailto:{{ $booking->email }}" class="text-amber-700">{{ $booking->email }}</a></dd></div>
                    <div><dt class="text-stone-500">Phone</dt><dd class="font-medium"><a href="tel:{{ preg_replace('/[^\d+]/', '', $booking->phone) }}" class="text-amber-700">{{ $booking->phone }}</a></dd></div>
                    <div><dt class="text-stone-500">WhatsApp</dt><dd class="font-medium">{{ $booking->whatsapp ?: '-' }}</dd></div>
                    <div><dt class="text-stone-500">Nationality</dt><dd class="font-medium">{{ $booking->nationality ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-stone-900">Price</h2>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-stone-100">
                        <tr><td class="py-2 text-stone-600">Subtotal (before discounts)</td><td class="py-2 text-right">{{ $money($booking->subtotal) }}</td></tr>
                        @foreach ($booking->addons as $line)
                            <tr><td class="py-2 text-stone-600">{{ $line->name_snapshot }} &times; {{ $line->quantity }}</td><td class="py-2 text-right">{{ $money($line->line_total) }}</td></tr>
                        @endforeach
                        @if ((float) $booking->discount_total > 0)
                            <tr><td class="py-2 text-stone-600">Discounts{{ $booking->coupon ? ' (coupon '.$booking->coupon->code.')' : '' }}</td><td class="py-2 text-right text-green-700">- {{ $money($booking->discount_total) }}</td></tr>
                        @endif
                        @if ((float) $booking->tax > 0)
                            <tr><td class="py-2 text-stone-600">Tax</td><td class="py-2 text-right">{{ $money($booking->tax) }}</td></tr>
                        @endif
                        <tr class="font-semibold"><td class="py-2">Grand total</td><td class="py-2 text-right">{{ $money($booking->grand_total) }}</td></tr>
                        <tr><td class="py-2 text-stone-600">Paid</td><td class="py-2 text-right">{{ $money($paid) }}</td></tr>
                        <tr class="font-semibold"><td class="py-2">Balance due</td><td class="py-2 text-right">{{ $money($balance) }}</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-stone-900">Payments</h2>

                @if ($booking->payments->isEmpty())
                    <p class="text-sm text-stone-500">No payments yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                                <tr><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Method</th><th class="py-2 pr-3">Amount</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Reference</th><th class="py-2"></th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($booking->payments as $p)
                                    <tr>
                                        <td class="py-2 pr-3">{{ ($p->paid_at ?? $p->created_at)?->format('d M Y') }}</td>
                                        <td class="py-2 pr-3">{{ $p->method->label() }}</td>
                                        <td class="py-2 pr-3">{{ $money($p->amount) }}</td>
                                        <td class="py-2 pr-3"><x-admin.badge :color="$txColors[$p->status->value]" :label="$p->status->label()" /></td>
                                        <td class="py-2 pr-3 text-stone-500">{{ $p->transaction_id ?: '-' }}</td>
                                        <td class="py-2">
                                            @if ($p->status === \App\Enums\PaymentTransactionStatus::Pending)
                                                <form method="POST" action="{{ route('admin.payments.complete', $p) }}" class="flex items-center gap-2">
                                                    @csrf @method('PATCH')
                                                    <input type="text" name="transaction_id" placeholder="Transaction ID" class="w-32 rounded-md border-stone-300 text-xs">
                                                    <button class="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700">Mark received</button>
                                                </form>
                                            @elseif ($p->status === \App\Enums\PaymentTransactionStatus::Completed)
                                                @can('update', $p)
                                                    <form method="POST" action="{{ route('admin.payments.refund', $p) }}" onsubmit="return confirm('Mark this payment as refunded?')">
                                                        @csrf @method('PATCH')
                                                        <button class="text-xs text-red-600 hover:underline">Mark refunded</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($canPay)
                    <form method="POST" action="{{ route('admin.bookings.payments.store', $booking) }}" class="mt-5 grid gap-3 border-t border-stone-100 pt-4 sm:grid-cols-4">
                        @csrf
                        <select name="method" class="{{ $in }}">
                            @foreach (\App\Enums\PaymentMethod::cases() as $m)
                                <option value="{{ $m->value }}">{{ $m->label() }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ $balance > 0 ? $balance : '' }}" placeholder="Amount" class="{{ $in }}">
                        <input type="text" name="transaction_id" placeholder="Transaction ID" class="{{ $in }}">
                        <input type="text" name="notes" placeholder="Notes" class="{{ $in }}">
                        <label class="flex items-center gap-2 text-sm sm:col-span-3">
                            <input type="checkbox" name="received" value="1" checked class="rounded border-stone-300 text-amber-600">
                            Money already received (untick to create a pending payment)
                        </label>
                        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Add payment</button>
                    </form>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-stone-900">Actions</h2>
                <div class="space-y-3 text-sm">
                    @can('update', $booking)
                        @if ($booking->status === \App\Enums\BookingStatus::Pending)
                            <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">@csrf
                                <button class="w-full rounded-md bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">Confirm booking</button>
                            </form>
                        @endif
                        @if ($booking->status === \App\Enums\BookingStatus::Confirmed)
                            <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">@csrf
                                <button class="w-full rounded-md bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Mark completed</button>
                            </form>
                        @endif
                        @if (in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::Completed], true))
                            <form method="POST" action="{{ route('admin.bookings.resend', $booking) }}">@csrf
                                <button class="w-full rounded-md border border-stone-300 px-4 py-2 hover:bg-stone-50">Resend notification</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </section>

            @if ($cancellable)
                @can('update', $booking)
                    <section class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-2 font-semibold text-stone-900">Cancel booking</h2>
                        @if ($quote)
                            <p class="mb-3 rounded bg-stone-50 p-3 text-xs text-stone-600">
                                @if ($quote->isFree)
                                    Free cancellation applies.
                                @else
                                    Late cancellation: fee {{ number_format($quote->feePercent) }}% ({{ $money($quote->feeAmount) }}).
                                @endif
                            </p>
                        @endif
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="space-y-3 text-sm"
                              onsubmit="return confirm('Cancel this booking and release its seats?')">
                            @csrf
                            <textarea name="reason" rows="2" placeholder="Reason (internal note)" class="{{ $in }}"></textarea>
                            <label class="flex items-center gap-2"><input type="checkbox" name="waive_fee" value="1" class="rounded border-stone-300 text-amber-600"> Waive the fee</label>
                            <button class="w-full rounded-md bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">Cancel booking</button>
                        </form>
                    </section>
                @endcan
            @endif

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold text-stone-900">Internal notes</h2>
                @can('update', $booking)
                    <form method="POST" action="{{ route('admin.bookings.notes', $booking) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <textarea name="admin_notes" rows="6" class="{{ $in }}">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Save notes</button>
                    </form>
                @else
                    <p class="whitespace-pre-line text-sm text-stone-600">{{ $booking->admin_notes ?: 'No notes.' }}</p>
                @endcan
            </section>
        </aside>
    </div>
</x-admin.layout>