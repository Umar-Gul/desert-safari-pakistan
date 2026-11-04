@php
    $stepBadge =
        'flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#8B2014] text-xs font-bold text-white';
    $stepTitle = 'text-sm font-bold text-stone-900';
    $inputClass =
        'block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:border-[#8B2014] focus:outline-none focus:ring-2 focus:ring-[#8B2014]/20';
    $stepBtn =
        'flex h-9 w-9 items-center justify-center rounded-full border border-stone-300 bg-white text-lg font-semibold leading-none text-stone-700 transition hover:border-[#8B2014] hover:text-[#8B2014] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-stone-300 disabled:hover:text-stone-700';
@endphp

@if ($errors->has('booking'))
    <p role="alert" class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
        {{ $errors->first('booking') }}</p>
@endif

<div x-data="bookingWidget(@js($widget), @js($errors->first('booking')))" class="overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-stone-200">

    {{-- Header --}}
    <div class="border-b border-stone-100 bg-gradient-to-br from-[#fbf3ea] to-white px-5 pb-3 pt-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">From</p>
        <x-price-badge :tour="$tour" size="lg" />
        <p class="mt-0.5 text-xs text-stone-500">Per person. Your final price is shown below.</p>
    </div>

    <div class="space-y-4 px-5 py-4">

        {{-- 1. Date --}}
        <section>
            <div class="mb-2 flex items-center gap-2.5">
                <span class="{{ $stepBadge }}">1</span>
                <h3 class="{{ $stepTitle }}"><label for="w_date">Choose your date</label></h3>
            </div>

            <div class="rounded-2xl border border-stone-200 p-2.5">
                {{-- Compact week strip --}}
                <div class="mb-2 flex items-center gap-2">
                    <button type="button" @click="prevWeek()" :disabled="!canPrevWeek" class="{{ $stepBtn }} !h-8 !w-8 shrink-0"
                        aria-label="Previous dates">&lsaquo;</button>
                    <p class="flex-1 text-center text-sm font-bold text-stone-900" x-text="stripLabel" aria-live="polite"></p>
                    <button type="button" @click="nextWeek()" :disabled="!canNextWeek" class="{{ $stepBtn }} !h-8 !w-8 shrink-0"
                        aria-label="Next dates">&rsaquo;</button>
                </div>

                <div id="w_date" role="group" aria-label="Choose a booking date" class="grid grid-cols-7 gap-1">
                    <template x-for="cell in stripCells" :key="cell.key">
                        <button type="button" @click="selectDate(cell.key)" :disabled="cell.status !== 'available'"
                            :aria-label="cellLabel(cell)" :title="cellLabel(cell)" :class="cellClass(cell)"
                            class="flex h-[60px] flex-col items-center justify-center rounded-xl border text-center leading-tight transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] disabled:cursor-not-allowed">
                            <span class="text-[10px] font-semibold uppercase" x-text="cell.dow"></span>
                            <span class="text-base font-bold" x-text="cell.day"></span>
                            <span class="max-w-full truncate text-[9px] font-semibold" x-text="cell.short"></span>
                        </button>
                    </template>
                </div>

                {{-- Optional full month calendar --}}
                <div x-show="showCal" x-cloak class="mt-3 border-t border-stone-100 pt-3">
                    <div class="mb-2 flex items-center justify-between">
                        <button type="button" @click="prevMonth()" :disabled="!canPrev" class="{{ $stepBtn }} !h-7 !w-7"
                            aria-label="Previous month">&lsaquo;</button>
                        <p class="text-xs font-bold text-stone-900" x-text="monthLabel"></p>
                        <button type="button" @click="nextMonth()" :disabled="!canNext" class="{{ $stepBtn }} !h-7 !w-7"
                            aria-label="Next month">&rsaquo;</button>
                    </div>
                    <div class="mb-1 grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase text-stone-400"
                        aria-hidden="true">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                            <span>{{ $dayName }}</span>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="cell in monthCells" :key="cell.key">
                            <button type="button" @click="selectDate(cell.key)"
                                :disabled="cell.blank || cell.status !== 'available'" :tabindex="cell.blank ? -1 : 0"
                                :aria-hidden="cell.blank ? 'true' : 'false'"
                                :aria-label="cell.blank ? null : cellLabel(cell)"
                                :title="cell.blank ? '' : cellLabel(cell)" :class="cellClass(cell)"
                                class="relative flex h-8 items-center justify-center rounded-lg border text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] disabled:cursor-not-allowed">
                                <span x-text="cell.blank ? '' : cell.day"></span>
                                <i x-show="!cell.blank && !cell.past && cell.status !== 'none' && cell.key !== date"
                                    class="absolute bottom-0.5 h-1 w-1 rounded-full" :class="dotClass(cell)"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-medium text-stone-600"
                aria-label="Availability key">
                <span class="inline-flex items-center gap-1.5"><i
                        class="h-2 w-2 rounded-full bg-emerald-600"></i>Available</span>
                <span class="inline-flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-amber-500"></i>Fully
                    Booked</span>
                <span class="inline-flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-red-600"></i>Closed</span>
                <button type="button" @click="showCal = !showCal" :aria-expanded="showCal"
                    class="ml-auto font-semibold text-[#8B2014] underline-offset-2 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]"
                    x-text="showCal ? 'Hide calendar' : 'Full calendar'"></button>
            </div>

            <p class="mt-2 flex items-start gap-1.5 text-xs font-medium" role="status" aria-live="polite"
                :class="!date ? 'text-stone-500' : (dateOk ? 'text-emerald-700' : 'text-red-600')">
                <svg class="mt-px h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path x-show="dateOk" fill-rule="evenodd"
                        d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z"
                        clip-rule="evenodd" />
                    <path x-show="!dateOk" fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-4a1 1 0 00-1 1v3a1 1 0 102 0V7a1 1 0 00-1-1zm0 7a1 1 0 100 2 1 1 0 000-2z"
                        clip-rule="evenodd" />
                </svg>
                <span x-text="serverError || dateStatus"></span>
            </p>
        </section>

        {{-- 2. Guests --}}
        <section>
            <div class="mb-2 flex items-center gap-2.5">
                <span class="{{ $stepBadge }}">2</span>
                <h3 class="{{ $stepTitle }}">Who is coming?</h3>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-stone-200 px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Adults</p>
                    <div class="mt-1.5 flex items-center justify-between">
                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8" :disabled="adults <= 1"
                            @click="adults = Math.max(1, adults - 1); schedule()" aria-label="Fewer adults">&minus;</button>
                        <span class="text-base font-bold text-stone-900" x-text="adults" aria-live="polite"></span>
                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8" :disabled="guests >= guestLimit"
                            @click="if (guests < guestLimit) { adults++; schedule(); }" aria-label="More adults">+</button>
                    </div>
                </div>
                <div class="rounded-2xl border border-stone-200 px-3 py-2.5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Children</p>
                    <div class="mt-1.5 flex items-center justify-between">
                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8" :disabled="children <= 0"
                            @click="children = Math.max(0, children - 1); schedule()" aria-label="Fewer children">&minus;</button>
                        <span class="text-base font-bold text-stone-900" x-text="children" aria-live="polite"></span>
                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8" :disabled="guests >= guestLimit"
                            @click="if (guests < guestLimit) { children++; schedule(); }" aria-label="More children">+</button>
                    </div>
                </div>
            </div>
            <p class="mt-2 text-xs text-stone-500">
                <span x-text="guests"></span> <span x-text="guests === 1 ? 'guest' : 'guests'"></span> selected<template
                    x-if="cfg.maxGuests"><span> &middot; up to <span x-text="cfg.maxGuests"></span> per
                        booking</span></template>
            </p>
        </section>

        {{-- 3. Options --}}
        <section x-show="cfg.campTypes.length || cfg.vehicles.length || cfg.addons.length" x-cloak>
            <div class="mb-2 flex items-center gap-2.5">
                <span class="{{ $stepBadge }}">3</span>
                <h3 class="{{ $stepTitle }}">Choose extras (optional)</h3>
            </div>

            <div class="space-y-3">
                <div x-show="cfg.campTypes.length">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500"
                        for="w_camp">Camp style</label>
                    <select id="w_camp" x-model="campTypeId" @change="schedule()" class="{{ $inputClass }}">
                        <option value="">Standard camp (included)</option>
                        <template x-for="c in cfg.campTypes" :key="c.id">
                            <option :value="String(c.id)" x-text="c.name + (c.note ? ' (' + c.note + ')' : '')">
                            </option>
                        </template>
                    </select>
                </div>

                <div x-show="cfg.vehicles.length">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500"
                        for="w_vehicle">Choose vehicle</label>
                    <select id="w_vehicle" x-model="vehicleId" @change="schedule()" class="{{ $inputClass }}">
                        <option value="">Standard vehicle (included)</option>
                        <template x-for="v in cfg.vehicles" :key="v.id">
                            <option :value="String(v.id)" x-text="v.name + (v.note ? ' (' + v.note + ')' : '')">
                            </option>
                        </template>
                    </select>
                </div>

                <div x-show="cfg.addons.length">
                    <p class="mb-1.5 text-xs font-semibold uppercase tracking-wider text-stone-500">Extra services</p>
                    <div class="space-y-2">
                        <template x-for="a in cfg.addons" :key="a.id">
                            <div class="rounded-xl border p-3 transition"
                                :class="addons[a.id] ? 'border-[#8B2014] bg-[#8B2014]/5' : 'border-stone-200 bg-white'">
                                <div class="flex items-center justify-between gap-3">
                                    <label class="flex min-w-0 cursor-pointer items-center gap-2.5">
                                        <input type="checkbox"
                                            class="h-4 w-4 rounded border-stone-300 text-[#8B2014] focus:ring-[#8B2014]"
                                            :checked="!!addons[a.id]" @change="toggleAddon(a)">
                                        <span class="truncate text-sm font-semibold text-stone-900"
                                            x-text="a.name"></span>
                                    </label>
                                    <span class="whitespace-nowrap text-sm font-semibold text-[#8B2014]"
                                        x-text="'+ ' + fmt(a.price)"></span>
                                </div>

                                <div class="mt-2.5 flex items-center justify-between border-t border-[#8B2014]/10 pt-2.5"
                                    x-show="addons[a.id]">
                                    <span class="text-xs text-stone-600">Quantity</span>
                                    <div class="flex items-center gap-2.5">
                                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8"
                                            :disabled="addons[a.id] <= 1"
                                            @click="addons[a.id] = Math.max(1, Number(addons[a.id]) - 1); schedule()"
                                            aria-label="Decrease quantity">&minus;</button>
                                        <span class="w-5 text-center text-sm font-bold text-stone-900"
                                            x-text="addons[a.id]"></span>
                                        <button type="button" class="{{ $stepBtn }} !h-8 !w-8"
                                            :disabled="addons[a.id] >= 20"
                                            @click="addons[a.id] = Math.min(20, Number(addons[a.id]) + 1); schedule()"
                                            aria-label="Increase quantity">+</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        {{-- Coupon --}}
        <section x-data="{ couponOpen: !!coupon }">
            <button type="button" @click="couponOpen = !couponOpen" :aria-expanded="couponOpen"
                class="flex w-full items-center justify-between rounded-xl text-sm font-semibold text-stone-700 transition hover:text-[#8B2014] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]">
                <span class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#8B2014]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M17.7 9.3l-7-7A1 1 0 0010 2H4a2 2 0 00-2 2v6c0 .27.1.52.3.7l7 7a1 1 0 001.4 0l7-7a1 1 0 000-1.4zM6.5 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"
                            clip-rule="evenodd" />
                    </svg>
                    Have a discount code?
                </span>
                <svg class="h-4 w-4 transition-transform" :class="couponOpen ? 'rotate-180' : ''" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="couponOpen" x-cloak class="mt-3">
                <label class="sr-only" for="w_coupon">Discount code</label>
                <div class="flex gap-2">
                    <input id="w_coupon" type="text" x-model="coupon" class="{{ $inputClass }}"
                        placeholder="Enter discount code" @keydown.enter.prevent="refresh()">
                    <button type="button" @click="refresh()"
                        class="inline-flex min-h-[44px] shrink-0 items-center justify-center rounded-xl border border-stone-300 bg-white px-4 text-sm font-semibold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]">Apply</button>
                </div>
                <p class="mt-1.5 text-xs text-red-600" x-show="quote && quote.coupon_error"
                    x-text="quote ? quote.coupon_error : ''"></p>
                <p class="mt-1.5 text-xs font-medium text-emerald-700"
                    x-show="quote && quote.coupon_code && !quote.coupon_error"
                    x-text="quote ? 'Discount code ' + quote.coupon_code + ' applied' : ''"></p>
            </div>
        </section>

        {{-- Price summary --}}
        <section class="rounded-2xl bg-stone-50 p-3.5 ring-1 ring-stone-200" aria-live="polite">
            <h3 class="mb-3 text-sm font-bold text-stone-900">Your total price</h3>

            <div x-show="loading" class="space-y-2" aria-label="Calculating price">
                <div class="h-4 w-full animate-pulse rounded bg-stone-200"></div>
                <div class="h-4 w-3/4 animate-pulse rounded bg-stone-200"></div>
                <div class="h-6 w-1/2 animate-pulse rounded bg-stone-200"></div>
            </div>

            <p x-show="!loading && error" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" x-text="error">
            </p>
            <p x-show="!loading && !error && !quote" class="text-sm text-stone-500">Choose an available date to see
                your total price.</p>

            <template x-if="quote && !loading && !error">
                <dl class="space-y-2 text-sm text-stone-700">
                    <div class="flex justify-between gap-3">
                        <dt x-text="quote.adults + ' adult(s), ' + quote.children + ' child(ren)'"></dt>
                        <dd class="font-medium" x-text="fmt(quote.people_gross)"></dd>
                    </div>
                    <div class="flex justify-between gap-3 text-emerald-700" x-show="quote.tour_discount > 0">
                        <dt>Offer discount</dt>
                        <dd class="font-medium" x-text="'- ' + fmt(quote.tour_discount)"></dd>
                    </div>
                    <div class="flex justify-between gap-3" x-show="quote.camp_amount > 0">
                        <dt>Camp upgrade</dt>
                        <dd class="font-medium" x-text="fmt(quote.camp_amount)"></dd>
                    </div>
                    <div class="flex justify-between gap-3" x-show="quote.vehicle_amount > 0">
                        <dt>Vehicle</dt>
                        <dd class="font-medium" x-text="fmt(quote.vehicle_amount)"></dd>
                    </div>
                    <template x-for="l in quote.addon_lines" :key="l.addon_id">
                        <div class="flex justify-between gap-3">
                            <dt x-text="l.name + ' x ' + l.quantity"></dt>
                            <dd class="font-medium" x-text="fmt(l.total)"></dd>
                        </div>
                    </template>
                    <div class="flex justify-between gap-3 text-emerald-700" x-show="quote.coupon_discount > 0">
                        <dt>Discount code</dt>
                        <dd class="font-medium" x-text="'- ' + fmt(quote.coupon_discount)"></dd>
                    </div>
                    <div class="flex justify-between gap-3" x-show="quote.tax > 0">
                        <dt x-text="'Tax (' + quote.tax_percent + '%)'"></dt>
                        <dd class="font-medium" x-text="fmt(quote.tax)"></dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 border-t border-stone-200 pt-3">
                        <dt class="text-base font-bold text-stone-900">Total</dt>
                        <dd class="text-2xl font-extrabold text-[#8B2014]" x-text="fmt(quote.grand_total)"></dd>
                    </div>
                </dl>
            </template>
        </section>

        {{-- Actions --}}
        <div>
            <button type="button" :disabled="!canBook" @click="goCheckout()"
                class="inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#8B2014] px-5 text-base font-bold text-white shadow-sm transition hover:bg-[#741a10] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-stone-300 disabled:shadow-none">
                <span x-text="canBook ? 'Book now - ' + fmt(quote.grand_total) : 'Book now'"></span>
            </button>

            <p class="mt-2 text-center text-xs font-medium text-stone-500" x-show="!canBook && !loading"
                x-text="!date ? 'Choose a date to continue' : (!dateOk ? 'Choose an available date to continue' : '')">
            </p>

            <ul class="mt-3 space-y-1 text-xs text-stone-600">
                <li class="flex items-start gap-2">
                    <svg class="mt-px h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z"
                            clip-rule="evenodd" />
                    </svg>
                    No payment now. We will confirm your booking first.
                </li>
                <li class="flex items-start gap-2">
                    <svg class="mt-px h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Free cancellation up to {{ config('booking.free_cancellation_hours', 24) }} hours before the tour starts.
                </li>
            </ul>

            <div class="my-3 flex items-center gap-3 text-xs uppercase tracking-wider text-stone-400"
                aria-hidden="true">
                <span class="h-px flex-1 bg-stone-200"></span>or<span class="h-px flex-1 bg-stone-200"></span>
            </div>

            <a href="{{ whatsapp_url('Hello, I would like to book: ' . $tour->title) }}" target="_blank"
                rel="noopener"
                class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl border border-[#25D366] bg-white px-5 text-sm font-semibold text-[#128C7E] transition hover:bg-[#25D366]/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path
                        d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.04 21.5a9.45 9.45 0 01-4.82-1.32l-.35-.2-3.58.94.96-3.49-.23-.36a9.45 9.45 0 1117.43-5.04c0 5.21-4.24 9.47-9.41 9.47zM12.04 0C5.46 0 .1 5.36.1 11.94c0 2.1.55 4.15 1.6 5.96L0 24l6.27-1.64a11.9 11.9 0 005.77 1.47h.01c6.58 0 11.94-5.36 11.94-11.94C23.99 5.36 18.63 0 12.04 0z" />
                </svg>
                Ask on WhatsApp
            </a>
        </div>
    </div>
</div>

@push('scripts')
    @verbatim
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('bookingWidget', (cfg, serverError = '') => ({
                    cfg,
                    serverError,
                    date: cfg.initial.date || '',
                    month: (cfg.initial.date || cfg.today).slice(0, 7),
                    weekStart: 0,
                    showCal: false,
                    adults: Number(cfg.initial.adults) || 2,
                    children: Number(cfg.initial.children) || 0,
                    campTypeId: cfg.initial.camp_type_id ? String(cfg.initial.camp_type_id) : '',
                    vehicleId: cfg.initial.vehicle_id ? String(cfg.initial.vehicle_id) : '',
                    addons: Array.isArray(cfg.initial.addons) ? {} : {
                        ...cfg.initial.addons
                    },
                    coupon: cfg.initial.coupon_code || '',
                    availability: Array.isArray(cfg.availability) ? {} : cfg.availability,
                    quote: null,
                    error: '',
                    loading: false,
                    seq: 0,
                    timer: null,

                    init() {
                        if (!this.months.includes(this.month)) {
                            this.month = this.months[0] || this.cfg.today.slice(0, 7);
                        }
                        this.focusStrip(this.date);
                        if (this.date && this.dateOk) {
                            this.capGuests();
                            this.refresh();
                        }
                    },

                    /* ---------- compact week strip ---------- */
                    get upcoming() {
                        return this.calendarDates.filter(d => d >= this.cfg.today);
                    },
                    get stripDates() {
                        return this.upcoming.slice(this.weekStart, this.weekStart + 7);
                    },
                    get stripLabel() {
                        const s = this.stripDates;
                        if (!s.length) return '';
                        return this.shortDate(s[0]) + ' - ' + this.shortDate(s[s.length - 1]);
                    },
                    get canPrevWeek() {
                        return this.weekStart > 0;
                    },
                    get canNextWeek() {
                        return this.weekStart + 7 < this.upcoming.length;
                    },
                    prevWeek() {
                        this.weekStart = Math.max(0, this.weekStart - 7);
                    },
                    nextWeek() {
                        if (this.canNextWeek) this.weekStart += 7;
                    },
                    focusStrip(d) {
                        const i = this.upcoming.indexOf(d);
                        if (i > -1) this.weekStart = Math.floor(i / 7) * 7;
                    },
                    get stripCells() {
                        return this.stripDates.map(key => {
                            const e = this.availability[key];
                            const status = e ? e.status : 'none';
                            return {
                                key,
                                blank: false,
                                past: false,
                                today: key === this.cfg.today,
                                status,
                                day: Number(key.slice(-2)),
                                dow: new Date(key + 'T00:00:00').toLocaleDateString('en-GB', {
                                    weekday: 'short'
                                }),
                                short: status === 'available' ? e.remaining + ' left' : (status === 'full' ?
                                    'Full' : 'Closed'),
                            };
                        });
                    },

                    get guests() {
                        return Number(this.adults) + Number(this.children);
                    },
                    get entry() {
                        return this.date ? this.availability[this.date] : null;
                    },
                    get dateOk() {
                        return !!(this.entry && this.entry.status === 'available');
                    },
                    get guestLimit() {
                        return Math.min(Number(this.cfg.maxGuests), this.dateOk ? Number(this.entry
                            .remaining) : Number(this.cfg.maxGuests));
                    },
                    get calendarDates() {
                        return Object.keys(this.availability).sort();
                    },
                    get canBook() {
                        return !!this.quote && !this.error && !this.loading;
                    },
                    get openDates() {
                        return this.calendarDates.filter(d => this.availability[d].status ===
                            'available').slice(0, 4);
                    },

                    /* ---------- compact month calendar ---------- */
                    get months() {
                        return [...new Set(this.calendarDates.map(d => d.slice(0, 7)))];
                    },
                    get monthLabel() {
                        const [y, m] = this.month.split('-').map(Number);
                        return new Date(y, m - 1, 1).toLocaleDateString('en-GB', {
                            month: 'long',
                            year: 'numeric'
                        });
                    },
                    get canPrev() {
                        return this.months.indexOf(this.month) > 0;
                    },
                    get canNext() {
                        const i = this.months.indexOf(this.month);
                        return i > -1 && i < this.months.length - 1;
                    },
                    prevMonth() {
                        if (this.canPrev) this.month = this.months[this.months.indexOf(this.month) - 1];
                    },
                    nextMonth() {
                        if (this.canNext) this.month = this.months[this.months.indexOf(this.month) + 1];
                    },
                    get monthCells() {
                        const [y, m] = this.month.split('-').map(Number);
                        const lead = (new Date(y, m - 1, 1).getDay() + 6) % 7; // Monday first
                        const days = new Date(y, m, 0).getDate();
                        const cells = [];

                        for (let i = 0; i < lead; i++) cells.push({
                            key: 'b' + i,
                            blank: true
                        });

                        for (let d = 1; d <= days; d++) {
                            const key = this.month + '-' + String(d).padStart(2, '0');
                            const e = this.availability[key];
                            cells.push({
                                key,
                                day: d,
                                blank: false,
                                status: e ? e.status : 'none',
                                past: key < this.cfg.today,
                                today: key === this.cfg.today,
                            });
                        }

                        return cells;
                    },
                    cellClass(cell) {
                        if (cell.blank) return 'invisible border-transparent';
                        if (cell.key === this.date) return 'border-[#8B2014] bg-[#8B2014] text-white';
                        let c = {
                            available: 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-500',
                            full: 'border-amber-200 bg-amber-50 text-amber-700',
                            closed: 'border-transparent bg-stone-100 text-stone-400',
                            none: 'border-transparent bg-transparent text-stone-300',
                        } [cell.status] || 'border-transparent bg-stone-100 text-stone-400';
                        if (cell.past) c += ' opacity-40';
                        if (cell.today) c += ' ring-1 ring-stone-400';
                        return c;
                    },
                    dotClass(cell) {
                        return {
                            available: 'bg-emerald-600',
                            full: 'bg-amber-500',
                            closed: 'bg-red-500',
                        } [cell.status] || '';
                    },
                    cellLabel(cell) {
                        const e = this.availability[cell.key];
                        const label = new Date(cell.key + 'T00:00:00').toLocaleDateString('en-GB', {
                            weekday: 'short',
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                        if (cell.past) return label + ' - Past date';
                        if (!e) return label + ' - Not on sale yet';
                        if (e.status === 'available') return label + ' - Available, ' + e.remaining + ' left';
                        return label + ' - ' + (e.status === 'full' ? 'Fully Booked' : 'Closed');
                    },

                    get dateStatus() {
                        if (!this.date) return 'Pick a date, or choose one of the next open dates.';
                        if (!this.entry) return 'This date is not on sale yet. Please choose another date.';
                        const label = new Date(this.date + 'T00:00:00').toLocaleDateString('en-GB', {
                            weekday: 'short',
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                        if (this.entry.status === 'closed') return label + ' - Closed, no availability.';
                        if (this.entry.status === 'full') return label + ' - Fully Booked, no availability.';
                        const price = this.entry.price_override !== null ? ' - Special price ' + this
                            .fmt(this.entry.price_override) : '';
                        return label + ' - Available, ' + this.entry.remaining + ' spots left' + price + '.';
                    },
                    selectDate(d) {
                        if (!this.availability[d] || this.availability[d].status !== 'available') return;
                        this.serverError = '';
                        this.date = d;
                        this.month = d.slice(0, 7);
                        this.focusStrip(d);
                        this.capGuests();
                        this.schedule();
                    },

                    capGuests() {
                        this.adults = Math.max(1, Math.min(this.adults, this.guestLimit));
                        this.children = Math.min(this.children, Math.max(0, this.guestLimit - this.adults));
                    },

                    fmt(n) {
                        return cfg.symbol + ' ' + Number(n).toLocaleString('en-US', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                    },
                    shortDate(d) {
                        return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', {
                            day: 'numeric',
                            month: 'short'
                        });
                    },

                    toggleAddon(a) {
                        if (this.addons[a.id]) {
                            delete this.addons[a.id];
                        } else {
                            this.addons[a.id] = 1;
                        }
                        this.schedule();
                    },

                    schedule() {
                        this.serverError = '';
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.refresh(), 300);
                    },

                    payload() {
                        const addons = {};
                        Object.entries(this.addons).forEach(([id, qty]) => {
                            if (Number(qty) > 0) addons[id] = Number(qty);
                        });

                        return {
                            tour_date: this.date,
                            adults: Number(this.adults),
                            children: Number(this.children),
                            camp_type_id: this.campTypeId || null,
                            vehicle_id: this.vehicleId || null,
                            addons,
                            coupon_code: this.coupon || null,
                        };
                    },

                    async refresh() {
                        this.quote = null;
                        this.error = '';

                        if (!this.date) return;
                        if (!this.dateOk) {
                            this.error = this.dateStatus;
                            return;
                        }

                        const id = ++this.seq;
                        this.loading = true;

                        try {
                            const res = await fetch(cfg.quoteUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': cfg.token
                                },
                                body: JSON.stringify(this.payload()),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (id !== this.seq) return;

                            if (!res.ok) {
                                const first = data.errors ? Object.values(data.errors)[0][0] : null;
                                this.serverError = data.message && !data.errors ? data.message : (
                                    first || 'Could not calculate the price.');
                                this.error = this.serverError;
                                return;
                            }

                            this.quote = data.price;
                        } catch (e) {
                            if (id === this.seq) this.error = 'Network error. Please try again.';
                        } finally {
                            if (id === this.seq) this.loading = false;
                        }
                    },

                    goCheckout() {
                        if (!this.canBook) return;

                        const p = this.payload();
                        const q = new URLSearchParams();
                        q.set('tour_date', p.tour_date);
                        q.set('adults', p.adults);
                        q.set('children', p.children);
                        if (p.camp_type_id) q.set('camp_type_id', p.camp_type_id);
                        if (p.vehicle_id) q.set('vehicle_id', p.vehicle_id);
                        Object.entries(p.addons).forEach(([id, qty]) => q.set('addons[' + id + ']', qty));
                        if (p.coupon_code) q.set('coupon_code', p.coupon_code);

                        window.location.href = cfg.checkoutUrl + '?' + q.toString();
                    },
                }));
            });
        </script>
    @endverbatim
@endpush