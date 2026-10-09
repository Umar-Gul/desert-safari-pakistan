<x-admin.layout title="Bookings">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">Bookings</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.export', request()->query()) }}" class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Export CSV</a>
            @can('create', \App\Models\Booking::class)
                <a href="{{ route('admin.bookings.create') }}" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">+ New booking</a>
            @endcan
        </div>
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Reference, name, email, phone" class="rounded-md border-stone-300 text-sm lg:col-span-2">
        <select name="status" class="rounded-md border-stone-300 text-sm">
            <option value="">Any status</option>
            @foreach (\App\Enums\BookingStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-md border-stone-300 text-sm">
            <option value="">Any payment</option>
            @foreach (\App\Enums\PaymentStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('payment_status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="tour_id" class="rounded-md border-stone-300 text-sm">
            <option value="">Any tour</option>
            @foreach ($tours as $tour)
                <option value="{{ $tour->id }}" @selected(request('tour_id') == $tour->id)>{{ $tour->title }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-md border-stone-300 text-sm" title="Tour date from">
            <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-md border-stone-300 text-sm" title="Tour date to">
        </div>
        <div class="sm:col-span-2 lg:col-span-6">
            <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Filter</button>
            <a href="{{ route('admin.bookings.index') }}" class="ml-2 text-sm text-stone-500 hover:underline">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Tour</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Guests</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="px-4 py-3"><a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-amber-700 hover:underline">{{ $booking->reference }}</a></td>
                        <td class="px-4 py-3">{{ $booking->customer_name }}<span class="block text-xs text-stone-500">{{ $booking->phone }}</span></td>
                        <td class="px-4 py-3">{{ $booking->tour?->title }}</td>
                        <td class="px-4 py-3">{{ $booking->tour_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $booking->guests_count }}</td>
                        <td class="px-4 py-3">{{ $booking->currency }} {{ number_format((float) $booking->grand_total) }}</td>
                        <td class="px-4 py-3"><x-admin.badge :color="$booking->status->color()" :label="$booking->status->label()" /></td>
                        <td class="px-4 py-3"><x-admin.badge :color="$booking->payment_status->color()" :label="$booking->payment_status->label()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-stone-500">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</x-admin.layout>