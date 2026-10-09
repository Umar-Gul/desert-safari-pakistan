@php
    $in = 'mt-1 block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500';
    $lbl = 'block text-sm font-medium text-stone-700';
@endphp

<x-admin.layout title="New booking">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-stone-900">New booking</h1>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-stone-600 hover:text-amber-700">&larr; All bookings</a>
    </div>

    @if ($errors->any() && ! $errors->has('booking'))
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">

        <section class="grid gap-4 rounded-lg bg-white p-6 shadow-sm md:grid-cols-2">
            <h2 class="font-semibold text-stone-900 md:col-span-2">Tour</h2>
            <div class="md:col-span-2">
                <label class="{{ $lbl }}" for="tour_id">Tour *</label>
                <select id="tour_id" name="tour_id" class="{{ $in }}">
                    <option value="">Choose...</option>
                    @foreach ($tours as $tour)
                        <option value="{{ $tour->id }}" @selected(old('tour_id') == $tour->id)>{{ $tour->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $lbl }}" for="tour_date">Tour date *</label>
                <input id="tour_date" name="tour_date" type="date" value="{{ old('tour_date') }}" class="{{ $in }}">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $lbl }}" for="adults">Adults *</label>
                    <input id="adults" name="adults" type="number" min="1" value="{{ old('adults', 2) }}" class="{{ $in }}">
                </div>
                <div>
                    <label class="{{ $lbl }}" for="children">Children</label>
                    <input id="children" name="children" type="number" min="0" value="{{ old('children', 0) }}" class="{{ $in }}">
                </div>
            </div>
            <div>
                <label class="{{ $lbl }}" for="camp_type_id">Camp type</label>
                <select id="camp_type_id" name="camp_type_id" class="{{ $in }}">
                    <option value="">None</option>
                    @foreach ($campTypes as $camp)
                        <option value="{{ $camp->id }}" @selected(old('camp_type_id') == $camp->id)>{{ $camp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $lbl }}" for="vehicle_id">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" class="{{ $in }}">
                    <option value="">None</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <p class="{{ $lbl }}">Add-ons (quantity)</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @forelse ($addons as $addon)
                        <label class="flex items-center justify-between gap-3 rounded border border-stone-200 px-3 py-2 text-sm">
                            <span>{{ $addon->name }} <span class="text-stone-400">({{ number_format((float) $addon->price) }})</span></span>
                            <input type="number" min="0" max="20" name="addons[{{ $addon->id }}]" value="{{ old("addons.{$addon->id}", 0) }}" class="w-20 rounded-md border-stone-300 text-sm">
                        </label>
                    @empty
                        <p class="text-sm text-stone-500">No add-ons available.</p>
                    @endforelse
                </div>
                <p class="mt-1 text-xs text-stone-500">Only add-ons attached to the chosen tour are accepted.</p>
            </div>
            <div>
                <label class="{{ $lbl }}" for="coupon_code">Coupon code</label>
                <input id="coupon_code" name="coupon_code" type="text" value="{{ old('coupon_code') }}" class="{{ $in }}">
            </div>
        </section>

        <section class="grid gap-4 rounded-lg bg-white p-6 shadow-sm md:grid-cols-2">
            <h2 class="font-semibold text-stone-900 md:col-span-2">Customer</h2>
            <div>
                <label class="{{ $lbl }}" for="customer_name">Full name *</label>
                <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="email">Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="phone">Phone *</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="whatsapp">WhatsApp</label>
                <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="nationality">Nationality</label>
                <input id="nationality" name="nationality" type="text" value="{{ old('nationality') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="city">Pickup city</label>
                <input id="city" name="city" type="text" list="cities" value="{{ old('city') }}" class="{{ $in }}">
                <datalist id="cities">@foreach ($cities as $city)<option value="{{ $city }}">@endforeach</datalist>
            </div>
            <div>
                <label class="{{ $lbl }}" for="pickup_time">Pickup time</label>
                <input id="pickup_time" name="pickup_time" type="time" value="{{ old('pickup_time') }}" class="{{ $in }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="pickup_location">Pickup location / hotel</label>
                <input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location') }}" class="{{ $in }}">
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lbl }}" for="pickup_address">Pickup address</label>
                <textarea id="pickup_address" name="pickup_address" rows="2" class="{{ $in }}">{{ old('pickup_address') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lbl }}" for="special_requests">Special requests</label>
                <textarea id="special_requests" name="special_requests" rows="2" class="{{ $in }}">{{ old('special_requests') }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm md:col-span-2">
                <input type="checkbox" name="confirm_now" value="1" @checked(old('confirm_now')) class="rounded border-stone-300 text-amber-600">
                Confirm the booking straight away
            </label>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Cancel</a>
            <button type="submit" class="rounded-md bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700">Create booking</button>
        </div>
    </form>
</x-admin.layout>