@php
    $txColors = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'stone'];
@endphp

<x-admin.layout title="Payments">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">Payments</h1>
        <p class="text-sm text-stone-600">Received this month: <strong>{{ config('booking.currency', 'PKR') }} {{ number_format($monthTotal) }}</strong></p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Booking ref, customer, transaction ID" class="w-64 rounded-md border-stone-300 text-sm">
        <select name="method" class="rounded-md border-stone-300 text-sm">
            <option value="">Any method</option>
            @foreach (\App\Enums\PaymentMethod::cases() as $m)
                <option value="{{ $m->value }}" @selected(request('method') === $m->value)>{{ $m->label() }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-stone-300 text-sm">
            <option value="">Any status</option>
            @foreach (\App\Enums\PaymentTransactionStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-3">Date</th><th class="px-4 py-3">Booking</th><th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Transaction</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($payments as $p)
                    <tr>
                        <td class="px-4 py-3">{{ ($p->paid_at ?? $p->created_at)?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($p->booking)
                                <a href="{{ route('admin.bookings.show', $p->booking_id) }}" class="font-medium text-amber-700 hover:underline">{{ $p->booking->reference }}</a>
                                <span class="block text-xs text-stone-500">{{ $p->booking->customer_name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $p->method->label() }}</td>
                        <td class="px-4 py-3">{{ $p->currency }} {{ number_format((float) $p->amount) }}</td>
                        <td class="px-4 py-3"><x-admin.badge :color="$txColors[$p->status->value]" :label="$p->status->label()" /></td>
                        <td class="px-4 py-3 text-stone-500">{{ $p->transaction_id ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-admin.layout>