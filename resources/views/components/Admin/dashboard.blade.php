<x-admin.layout title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Dashboard</h1>
        <p class="text-sm text-stone-500">{{ now()->format('l, d F Y') }}</p>
    </div>

    @if (count($cards))
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                @php $tag = $card['href'] ? 'a' : 'div'; @endphp
                <{{ $tag }} @if ($card['href']) href="{{ $card['href'] }}" @endif
                    class="block rounded-lg bg-white p-5 shadow-sm {{ $card['href'] ? 'transition hover:shadow-md' : '' }}">
                    <p class="text-sm text-stone-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $card['value'] }}</p>
                    @if ($card['hint'])
                        <p class="mt-1 text-xs text-stone-400">{{ $card['hint'] }}</p>
                    @endif
                </{{ $tag }}>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-white p-6 text-sm text-stone-600 shadow-sm">
            Welcome. Use the menu on the left to manage content.
        </div>
    @endif

    @if ($chart)
        <section class="mt-6 rounded-lg bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-stone-900">New bookings, last 30 days</h2>
            <div class="h-64"><canvas id="bookingsChart"></canvas></div>
        </section>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
            <script>
                new Chart(document.getElementById('bookingsChart'), {
                    type: 'line',
                    data: {
                        labels: @json($chart['labels']),
                        datasets: [{
                            label: 'New bookings',
                            data: @json($chart['values']),
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.12)',
                            fill: true,
                            tension: 0.3,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });
            </script>
        @endpush
    @endif

    @if ($upcoming !== null)
        <section class="mt-6 rounded-lg bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-stone-900">Tours in the next 7 days</h2>

            @if ($upcoming->isEmpty())
                <p class="text-sm text-stone-500">No pending or confirmed tours in the next 7 days.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                            <tr>
                                <th class="py-2 pr-4">Reference</th>
                                <th class="py-2 pr-4">Customer</th>
                                <th class="py-2 pr-4">Tour</th>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Guests</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($upcoming as $booking)
                                <tr>
                                    <td class="py-2 pr-4 font-medium">{{ $booking->reference }}</td>
                                    <td class="py-2 pr-4">{{ $booking->customer_name }}</td>
                                    <td class="py-2 pr-4">{{ $booking->tour?->title }}</td>
                                    <td class="py-2 pr-4">{{ $booking->tour_date->format('d M') }}{{ $booking->pickup_time ? ', '.$booking->pickup_time : '' }}</td>
                                    <td class="py-2 pr-4">{{ $booking->guests_count }}</td>
                                    <td class="py-2"><x-admin.badge :color="$booking->status->color()" :label="$booking->status->label()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
</x-admin.layout>