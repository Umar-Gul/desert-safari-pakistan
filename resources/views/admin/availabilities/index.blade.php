@php
    $in = 'block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500';
    $prev = $month->copy()->subMonth()->format('Y-m');
    $next = $month->copy()->addMonth()->format('Y-m');
@endphp

<x-admin.layout title="Availability">
    <h1 class="mb-6 text-2xl font-bold text-stone-900">Availability</h1>

    @if ($errors->any() && ! $errors->has('booking'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs font-medium text-stone-500" for="tour_id">Tour</label>
            <select id="tour_id" name="tour_id" class="mt-1 rounded-md border-stone-300 text-sm">
                @foreach ($tours as $t)
                    <option value="{{ $t->id }}" @selected($tour && $tour->id === $t->id)>{{ $t->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-stone-500" for="month">Month</label>
            <input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}" class="mt-1 rounded-md border-stone-300 text-sm">
        </div>
        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Show</button>
    </form>

    @if (! $tour)
        <div class="rounded-lg bg-white p-6 text-sm text-stone-600 shadow-sm">Create a tour first, then manage its dates here.</div>
    @else
        <div class="grid gap-6 xl:grid-cols-3"
             x-data="{ from: '{{ $month->toDateString() }}', to: '{{ $month->copy()->endOfMonth()->toDateString() }}', action: 'set' }">

            <section class="rounded-lg bg-white p-5 shadow-sm xl:col-span-2">
                <div class="mb-3 flex items-center justify-between">
                    <a href="{{ route('admin.availabilities.index', ['tour_id' => $tour->id, 'month' => $prev]) }}" class="text-sm text-amber-700 hover:underline">&larr; {{ $month->copy()->subMonth()->format('M Y') }}</a>
                    <h2 class="font-semibold text-stone-900">{{ $month->format('F Y') }}</h2>
                    <a href="{{ route('admin.availabilities.index', ['tour_id' => $tour->id, 'month' => $next]) }}" class="text-sm text-amber-700 hover:underline">{{ $month->copy()->addMonth()->format('M Y') }} &rarr;</a>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold uppercase text-stone-500">
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $name)<div class="py-1">{{ $name }}</div>@endforeach
                </div>

                <div class="grid grid-cols-7 gap-1">
                    @for ($i = 0; $i < $offset; $i++)<div></div>@endfor

                    @foreach ($days as $d)
                        @php
                            $full = $d['exists'] && ! $d['closed'] && $d['booked'] >= $d['capacity'];
                            $state = ! $d['exists'] ? 'bg-stone-50 text-stone-400'
                                : ($d['closed'] ? 'bg-red-100 text-red-800'
                                : ($full ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800'));
                        @endphp
                        <button type="button" @click="from = '{{ $d['date'] }}'; to = '{{ $d['date'] }}'"
                                class="min-h-[4.25rem] rounded-md p-1.5 text-left text-xs transition hover:ring-2 hover:ring-amber-400 {{ $state }}">
                            <span class="block font-semibold">{{ $d['day'] }}</span>
                            @if ($d['exists'])
                                <span class="block">{{ $d['booked'] }}/{{ $d['capacity'] }}</span>
                                @if ($d['closed'])<span class="block font-medium">Closed</span>@endif
                                @if ($d['price'] !== null)<span class="block">{{ number_format((float) $d['price']) }}</span>@endif
                            @else
                                <span class="block">-</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <p class="mt-4 flex flex-wrap gap-4 text-xs text-stone-600">
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-green-100 align-middle"></span>Open (booked/capacity)</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-amber-100 align-middle"></span>Full</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-red-100 align-middle"></span>Closed</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded bg-stone-100 align-middle"></span>Not on sale</span>
                </p>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm">
                <h2 class="mb-1 font-semibold text-stone-900">Update dates</h2>
                @if ($canEdit)
                    <p class="mb-4 text-xs text-stone-500">Click a day to fill the dates, or type a range.</p>

                    <form method="POST" action="{{ route('admin.availabilities.bulk') }}" class="space-y-4 text-sm">
                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->id }}">

                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="text-xs text-stone-500">From</label><input type="date" name="from" x-model="from" class="{{ $in }}"></div>
                            <div><label class="text-xs text-stone-500">To</label><input type="date" name="to" x-model="to" class="{{ $in }}"></div>
                        </div>

                        <div>
                            <p class="mb-1 text-xs text-stone-500">Only these weekdays (none ticked = every day)</p>
                            <div class="flex flex-wrap gap-x-3 gap-y-1">
                                @foreach ([1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 0 => 'Sun'] as $num => $name)
                                    <label class="flex items-center gap-1"><input type="checkbox" name="weekdays[]" value="{{ $num }}" class="rounded border-stone-300 text-amber-600"> {{ $name }}</label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-stone-500" for="action">Action</label>
                            <select id="action" name="action" x-model="action" class="{{ $in }}">
                                <option value="set">Set capacity (create or update, opens the date)</option>
                                <option value="close">Close dates</option>
                                <option value="open">Re-open dates</option>
                                <option value="delete">Remove dates (only those without bookings)</option>
                            </select>
                        </div>

                        <div x-show="action === 'set'" class="space-y-3">
                            <div><label class="text-xs text-stone-500">Capacity (guests per date)</label><input type="number" min="0" name="capacity" value="40" class="{{ $in }}"></div>
                            <div><label class="text-xs text-stone-500">Adult price for these dates (optional)</label><input type="number" step="0.01" min="0" name="price_override" class="{{ $in }}"></div>
                            <label class="flex items-center gap-2"><input type="checkbox" name="clear_price" value="1" class="rounded border-stone-300 text-amber-600"> Remove existing price overrides</label>
                            <p class="text-xs text-stone-500">Capacity never goes below guests already booked.</p>
                        </div>

                        <button class="w-full rounded-md bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Apply</button>
                    </form>
                @else
                    <p class="text-sm text-stone-500">You can view the calendar but not change it.</p>
                @endif
            </section>
        </div>
    @endif
</x-admin.layout>