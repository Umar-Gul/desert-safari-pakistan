@extends('layouts.frontend')

@section('title', 'Checkout: '.$tour->title)
@section('robots', 'noindex,nofollow')

@php
    $money = fn ($n) => money($n);
    $freeHours = (int) config('booking.free_cancellation_hours', 24);

    // Design helpers only. The name $input is NOT used, because the controller passes $input.
    $fieldBase = 'block w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:outline-none focus:ring-2';
    $fieldClass = fn (string $name) => $fieldBase . ($errors->has($name)
        ? ' border-red-400 focus:border-red-500 focus:ring-red-200'
        : ' border-stone-300 focus:border-[#8B2014] focus:ring-[#8B2014]/20');
    $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500';
    $cardClass = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200 sm:p-6';
    $stepBadge = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#8B2014] text-xs font-bold text-white';
    $checkIcon = '<svg class="mt-px h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>';
@endphp

@section('content')
{{-- Header strip --}}
<div class="border-b border-stone-200 bg-gradient-to-b from-[#fbf3ea] to-white">
    <div class="mx-auto max-w-7xl px-4 pb-8 pt-6">
        <x-breadcrumbs :items="[['All activities', route('tours.index')], [$tour->title, route('tours.show', $tour->slug)], ['Checkout', null]]" />

        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">Complete your booking</h1>
        <p class="mt-1.5 text-sm text-stone-600">Just a few details and we will confirm your booking. You pay nothing today.</p>
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
    <x-flash class="mb-4" />

    @if ($couponWarning)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.26 3.1c.75-1.34 2.73-1.34 3.48 0l6.1 10.9c.74 1.33-.22 2.99-1.74 2.99H3.9c-1.52 0-2.48-1.66-1.74-2.99L8.26 3.1zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
            <p>{{ $couponWarning }} The coupon was not applied.</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <p class="font-semibold">We could not submit your booking:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3 lg:items-start">

        {{-- Form (same markup logic as the original) --}}
        <form method="POST" action="{{ route('tours.checkout.store', $tour->slug) }}" class="min-w-0 space-y-6 lg:col-span-2">
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

            {{-- Your details --}}
            <section class="{{ $cardClass }}">
                <div class="mb-5 flex items-center gap-3">
                    <span class="{{ $stepBadge }}">1</span>
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">Your details</h2>
                        <p class="text-xs text-stone-500">We use these to confirm your booking.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="customer_name">Full name <span class="text-[#8B2014]">*</span></label>
                        <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required autocomplete="name" class="{{ $fieldClass('customer_name') }}">
                        @error('customer_name')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="email">Email <span class="text-[#8B2014]">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $fieldClass('email') }}">
                        @error('email')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="phone">Phone <span class="text-[#8B2014]">*</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+92 ..." class="{{ $fieldClass('phone') }}">
                        @error('phone')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="whatsapp">WhatsApp <span class="font-normal normal-case tracking-normal text-stone-400">(if different)</span></label>
                        <input id="whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp') }}" class="{{ $fieldClass('whatsapp') }}">
                        @error('whatsapp')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="nationality">Nationality</label>
                        <input id="nationality" name="nationality" type="text" value="{{ old('nationality') }}" class="{{ $fieldClass('nationality') }}">
                        @error('nationality')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Pickup --}}
            <section class="{{ $cardClass }}">
                <div class="mb-5 flex items-center gap-3">
                    <span class="{{ $stepBadge }}">2</span>
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">Pickup and requests</h2>
                        <p class="text-xs text-stone-500">Tell us where and when to meet you.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="city">Pickup city</label>
                        @if (count($cities))
                            <select id="city" name="city" class="{{ $fieldClass('city') }}">
                                <option value="">Choose...</option>
                                @foreach ($cities as $city)<option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>@endforeach
                            </select>
                        @else
                            <input id="city" name="city" type="text" value="{{ old('city') }}" class="{{ $fieldClass('city') }}">
                        @endif
                        @error('city')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="pickup_time">Preferred pickup time</label>
                        <input id="pickup_time" name="pickup_time" type="time" value="{{ old('pickup_time') }}" class="{{ $fieldClass('pickup_time') }}">
                        @error('pickup_time')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="pickup_location">Hotel or pickup point</label>
                        <input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location') }}" class="{{ $fieldClass('pickup_location') }}">
                        @error('pickup_location')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="pickup_address">Address</label>
                        <textarea id="pickup_address" name="pickup_address" rows="2" class="{{ $fieldClass('pickup_address') }}">{{ old('pickup_address') }}</textarea>
                        @error('pickup_address')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="special_requests">Special requests</label>
                        <textarea id="special_requests" name="special_requests" rows="3" class="{{ $fieldClass('special_requests') }}" placeholder="Dietary needs, accessibility, celebrations...">{{ old('special_requests') }}</textarea>
                        @error('special_requests')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Terms and submit --}}
            <section class="{{ $cardClass }}">
                <label class="flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-stone-700">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms'))
                           class="mt-0.5 h-5 w-5 shrink-0 rounded border-stone-300 text-[#8B2014] focus:ring-[#8B2014]">
                    <span>I accept the
                        <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="font-semibold text-[#8B2014] underline underline-offset-2">terms and conditions</a>, the
                        <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="font-semibold text-[#8B2014] underline underline-offset-2">privacy policy</a> and the
                        <a href="{{ route('refund') }}" target="_blank" rel="noopener" class="font-semibold text-[#8B2014] underline underline-offset-2">cancellation policy</a>.</span>
                </label>
                @error('terms')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                <button type="submit"
                        class="mt-6 inline-flex min-h-[52px] w-full items-center justify-center rounded-xl bg-[#8B2014] px-6 text-base font-bold text-white shadow-sm transition hover:bg-[#741a10] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2 sm:w-auto sm:min-w-[260px]">
                    Confirm booking request
                </button>

                <p class="mt-3 text-xs text-stone-500">You will not be charged now. We will email you once your booking is confirmed.</p>
            </section>
        </form>

        {{-- Summary (display only) --}}
        <aside class="lg:sticky lg:top-24 lg:col-span-1">
            <div class="overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-stone-200">

                <div class="flex gap-4 border-b border-stone-100 bg-gradient-to-br from-[#fbf3ea] to-white p-5">
                    <div class="h-20 w-24 shrink-0 overflow-hidden rounded-xl bg-stone-100 ring-1 ring-stone-200">
                        <x-cover :model="$tour" conversion="thumb" class="h-full w-full object-cover" />
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-bold leading-snug text-stone-900">{{ $tour->title }}</h2>
                        <p class="mt-1 inline-flex items-center gap-1.5 text-xs font-medium text-stone-600">
                            <svg class="h-4 w-4 text-[#8B2014]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                            {{ $input->tourDate->format('l, d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    <dl class="space-y-2 text-sm text-stone-700">
                        <div class="flex justify-between gap-3"><dt>{{ $price->adults }} adult(s), {{ $price->children }} child(ren)</dt><dd class="font-medium">{{ $money($price->peopleGross) }}</dd></div>
                        @if ($price->tourDiscount > 0)<div class="flex justify-between gap-3 text-emerald-700"><dt>Tour discount</dt><dd class="font-medium">- {{ $money($price->tourDiscount) }}</dd></div>@endif
                        @if ($price->campAmount > 0)<div class="flex justify-between gap-3"><dt>Camp: {{ $campName }}</dt><dd class="font-medium">{{ $money($price->campAmount) }}</dd></div>@endif
                        @if ($price->vehicleAmount > 0)<div class="flex justify-between gap-3"><dt>Vehicle: {{ $vehicleName }}</dt><dd class="font-medium">{{ $money($price->vehicleAmount) }}</dd></div>@endif
                        @foreach ($price->addonLines as $line)<div class="flex justify-between gap-3"><dt>{{ $line['name'] }} &times; {{ $line['quantity'] }}</dt><dd class="font-medium">{{ $money($line['total']) }}</dd></div>@endforeach
                        @if ($price->couponDiscount > 0)<div class="flex justify-between gap-3 text-emerald-700"><dt>Coupon {{ $price->couponCode }}</dt><dd class="font-medium">- {{ $money($price->couponDiscount) }}</dd></div>@endif
                        @if ($price->tax > 0)<div class="flex justify-between gap-3"><dt>Tax ({{ rtrim(rtrim(number_format($price->taxPercent, 2), '0'), '.') }}%)</dt><dd class="font-medium">{{ $money($price->tax) }}</dd></div>@endif
                        <div class="flex items-baseline justify-between gap-3 border-t border-stone-200 pt-3">
                            <dt class="text-base font-bold text-stone-900">Total</dt>
                            <dd class="text-2xl font-extrabold text-[#8B2014]">{{ $money($price->grandTotal) }}</dd>
                        </div>
                    </dl>

                    <ul class="space-y-2 rounded-2xl bg-stone-50 p-4 text-xs leading-relaxed text-stone-600 ring-1 ring-stone-200">
                        <li class="flex gap-2">{!! $checkIcon !!}<span><strong class="text-stone-800">No payment now.</strong> We confirm availability, then send payment details{{ count($methods) ? ' ('.implode(', ', $methods).')' : '' }}.</span></li>
                        <li class="flex gap-2">{!! $checkIcon !!}<span>Free cancellation up to {{ $freeHours }} hours before the start time.</span></li>
                    </ul>

                    <a href="{{ route('tours.show', $tour->slug) }}#book"
                       class="inline-flex w-full items-center justify-center gap-1.5 text-sm font-semibold text-[#8B2014] underline-offset-2 hover:underline">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 01-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        Change selection
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection