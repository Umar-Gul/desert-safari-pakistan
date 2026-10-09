@extends('layouts.frontend')

@section('title', 'Checkout: '.$tour->title)
@section('robots', 'noindex,nofollow')

@php
    $money = fn ($n) => money($n);
    $freeHours = (int) config('booking.free_cancellation_hours', 24);
@endphp

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <x-breadcrumbs :items="[['All activities', route('tours.index')], [$tour->title, route('tours.show', $tour->slug)], ['Checkout', null]]" />
    <h1 class="mb-6 mt-3 text-3xl font-extrabold text-stone-900">Complete your booking</h1>

    <x-flash />

    @if ($couponWarning)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $couponWarning }} The coupon was not applied.</div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">
        <form method="POST" action="{{ route('tours.checkout.store', $tour->slug) }}" class="space-y-6 lg:col-span-2">
            @csrf

            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <input type="hidden" name="tour_date" value="{{ $input->tourDate->toDateString() }}">
            <input type="hidden" name="adults" value="{{ $input->adults }}">
            <input type="hidden" name="children" value="{{ $input->children }}">
            @if ($input->campTypeId)<input type="hidden" name="camp_type_id" value="{{ $input->campTypeId }}">@endif
            @if ($input->vehicleId)<input type="hidden" name="vehicle_id" value="{{ $input->vehicleId }}">@endif
            @foreach ($input->addons as $addonId => $qty)<input type="hidden" name="addons[{{ $addonId }}]" value="{{ $qty }}">@endforeach
            @if ($input->couponCode)<input type="hidden" name="coupon_code" value="{{ $input->couponCode }}">@endif
            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

            <section class="grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 sm:grid-cols-2">
                <h2 class="text-lg font-bold text-stone-900 sm:col-span-2">Your details</h2>
                <div>
                    <label class="field-label" for="customer_name">Full name *</label>
                    <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required autocomplete="name" class="field">
                    @error('customer_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="email">Email *</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="phone">Phone *</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+92 ..." class="field">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="whatsapp">WhatsApp (if different)</label>
                    <input id="whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp') }}" class="field">
                    @error('whatsapp')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label" for="nationality">Nationality</label>
                    <input id="nationality" name="nationality" type="text" value="{{ old('nationality') }}" class="field">
                </div>
            </section>

            <section class="grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 sm:grid-cols-2">
                <h2 class="text-lg font-bold text-stone-900 sm:col-span-2">Pickup</h2>
                <div>
                    <label class="field-label" for="city">Pickup city</label>
                    @if (count($cities))
                        <select id="city" name="city" class="field">
                            <option value="">Choose...</option>
                            @foreach ($cities as $city)<option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>@endforeach
                        </select>
                    @else
                        <input id="city" name="city" type="text" value="{{ old('city') }}" class="field">
                    @endif
                    @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="pickup_time">Preferred pickup time</label>
                    <input id="pickup_time" name="pickup_time" type="time" value="{{ old('pickup_time') }}" class="field">
                    @error('pickup_time')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label" for="pickup_location">Hotel or pickup point</label>
                    <input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location') }}" class="field">
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label" for="pickup_address">Address</label>
                    <textarea id="pickup_address" name="pickup_address" rows="2" class="field">{{ old('pickup_address') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label" for="special_requests">Special requests</label>
                    <textarea id="special_requests" name="special_requests" rows="3" class="field" placeholder="Dietary needs, accessibility, celebrations...">{{ old('special_requests') }}</textarea>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                <label class="flex items-start gap-3 text-sm text-stone-700">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 rounded border-stone-300 text-amber-600">
                    <span>I accept the <a href="{{ route('terms') }}" target="_blank" class="text-amber-700 underline">terms and conditions</a>, the <a href="{{ route('privacy') }}" target="_blank" class="text-amber-700 underline">privacy policy</a> and the <a href="{{ route('refund') }}" target="_blank" class="text-amber-700 underline">cancellation policy</a>.</span>
                </label>
                @error('terms')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

                <button class="btn btn-primary mt-5 w-full sm:w-auto">Confirm booking request</button>
            </section>
        </form>

        {{-- Summary --}}
        <aside class="h-fit rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 lg:sticky lg:top-24">
            <div class="flex gap-3">
                <div class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-stone-100"><x-cover :model="$tour" conversion="thumb" class="h-full w-full object-cover" /></div>
                <div>
                    <h2 class="font-bold leading-snug text-stone-900">{{ $tour->title }}</h2>
                    <p class="text-xs text-stone-500">{{ $input->tourDate->format('l, d M Y') }}</p>
                </div>
            </div>

            <dl class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between"><dt>{{ $price->adults }} adult(s), {{ $price->children }} child(ren)</dt><dd>{{ $money($price->peopleGross) }}</dd></div>
                @if ($price->tourDiscount > 0)<div class="flex justify-between text-green-700"><dt>Tour discount</dt><dd>- {{ $money($price->tourDiscount) }}</dd></div>@endif
                @if ($price->campAmount > 0)<div class="flex justify-between"><dt>Camp: {{ $campName }}</dt><dd>{{ $money($price->campAmount) }}</dd></div>@endif
                @if ($price->vehicleAmount > 0)<div class="flex justify-between"><dt>Vehicle: {{ $vehicleName }}</dt><dd>{{ $money($price->vehicleAmount) }}</dd></div>@endif
                @foreach ($price->addonLines as $line)<div class="flex justify-between"><dt>{{ $line['name'] }} &times; {{ $line['quantity'] }}</dt><dd>{{ $money($line['total']) }}</dd></div>@endforeach
                @if ($price->couponDiscount > 0)<div class="flex justify-between text-green-700"><dt>Coupon {{ $price->couponCode }}</dt><dd>- {{ $money($price->couponDiscount) }}</dd></div>@endif
                @if ($price->tax > 0)<div class="flex justify-between"><dt>Tax ({{ rtrim(rtrim(number_format($price->taxPercent, 2), '0'), '.') }}%)</dt><dd>{{ $money($price->tax) }}</dd></div>@endif
                <div class="flex justify-between border-t border-stone-200 pt-2 text-lg font-extrabold text-stone-900"><dt>Total</dt><dd>{{ $money($price->grandTotal) }}</dd></div>
            </dl>

            <div class="mt-4 space-y-2 rounded-xl bg-stone-50 p-4 text-xs text-stone-600">
                <p><strong>No payment now.</strong> We confirm availability, then send payment details{{ count($methods) ? ' ('.implode(', ', $methods).')' : '' }}.</p>
                <p>Free cancellation up to {{ $freeHours }} hours before the start time.</p>
            </div>

            <a href="{{ route('tours.show', $tour->slug) }}#book" class="mt-4 block text-center text-sm text-amber-700 hover:underline">&larr; Change selection</a>
        </aside>
    </div>
</div>
@endsection