<div x-data="bookingWidget(@js($widget))" class="rounded-2xl bg-white p-5 shadow-lg ring-1 ring-stone-200 lg:sticky lg:top-24">
    <x-price-badge :tour="$tour" size="lg" />
    <p class="text-xs text-stone-500">Per person. Final price is confirmed below.</p>

    <div class="mt-5 space-y-4 text-sm">
        {{-- Date --}}
        <div>
            <label class="field-label" for="w_date">Tour date</label>
            <input id="w_date" type="date" x-model="date" :min="cfg.today" :max="cfg.lastDate" @change="schedule()" class="field">
            <p class="mt-1 text-xs" :class="dateOk ? 'text-green-700' : 'text-stone-500'" x-text="dateStatus"></p>
            <div class="mt-2 flex flex-wrap gap-1" x-show="openDates.length">
                <template x-for="d in openDates" :key="d">
                    <button type="button" @click="date = d; schedule()" :class="date === d ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-700'"
                            class="rounded-full px-2.5 py-1 text-xs" x-text="shortDate(d)"></button>
                </template>
            </div>
        </div>

        {{-- Guests --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="field-label">Adults</label>
                <div class="flex items-center rounded-lg border border-stone-300">
                    <button type="button" class="px-3 py-2" @click="adults = Math.max(1, adults - 1); schedule()" aria-label="Fewer adults">-</button>
                    <span class="flex-1 text-center font-semibold" x-text="adults"></span>
                    <button type="button" class="px-3 py-2" @click="if (guests < cfg.maxGuests) { adults++; schedule(); }" aria-label="More adults">+</button>
                </div>
            </div>
            <div>
                <label class="field-label">Children</label>
                <div class="flex items-center rounded-lg border border-stone-300">
                    <button type="button" class="px-3 py-2" @click="children = Math.max(0, children - 1); schedule()" aria-label="Fewer children">-</button>
                    <span class="flex-1 text-center font-semibold" x-text="children"></span>
                    <button type="button" class="px-3 py-2" @click="if (guests < cfg.maxGuests) { children++; schedule(); }" aria-label="More children">+</button>
                </div>
            </div>
        </div>

        {{-- Camp and vehicle --}}
        <div x-show="cfg.campTypes.length">
            <label class="field-label" for="w_camp">Camp type</label>
            <select id="w_camp" x-model="campTypeId" @change="schedule()" class="field">
                <option value="">Standard</option>
                <template x-for="c in cfg.campTypes" :key="c.id"><option :value="String(c.id)" x-text="c.name + (c.note ? ' (' + c.note + ')' : '')"></option></template>
            </select>
        </div>
        <div x-show="cfg.vehicles.length">
            <label class="field-label" for="w_vehicle">Vehicle</label>
            <select id="w_vehicle" x-model="vehicleId" @change="schedule()" class="field">
                <option value="">Included vehicle</option>
                <template x-for="v in cfg.vehicles" :key="v.id"><option :value="String(v.id)" x-text="v.name + (v.note ? ' (' + v.note + ')' : '')"></option></template>
            </select>
        </div>

        {{-- Add-ons --}}
        <div x-show="cfg.addons.length">
            <p class="field-label">Add-ons</p>
            <div class="space-y-2">
                <template x-for="a in cfg.addons" :key="a.id">
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-stone-200 px-3 py-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-stone-300 text-amber-600" :checked="!!addons[a.id]" @change="toggleAddon(a)">
                            <span x-text="a.name"></span>
                        </label>
                        <span class="flex items-center gap-2">
                            <span class="text-xs text-stone-500" x-text="fmt(a.price)"></span>
                            <input type="number" min="1" max="20" class="w-14 rounded border-stone-300 px-1 py-0.5 text-xs" x-show="addons[a.id]" x-model.number="addons[a.id]" @input="schedule()" aria-label="Quantity">
                        </span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Coupon --}}
        <div>
            <label class="field-label" for="w_coupon">Coupon code</label>
            <div class="flex gap-2">
                <input id="w_coupon" type="text" x-model="coupon" class="field" placeholder="Optional" @keydown.enter.prevent="refresh()">
                <button type="button" class="btn btn-outline" @click="refresh()">Apply</button>
            </div>
            <p class="mt-1 text-xs text-red-600" x-show="quote && quote.coupon_error" x-text="quote ? quote.coupon_error : ''"></p>
            <p class="mt-1 text-xs text-green-700" x-show="quote && quote.coupon_code && !quote.coupon_error" x-text="quote ? 'Coupon ' + quote.coupon_code + ' applied' : ''"></p>
        </div>
    </div>

    {{-- Price --}}
    <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm" aria-live="polite">
        <p x-show="loading" class="text-stone-500">Calculating...</p>
        <p x-show="!loading && error" class="text-red-600" x-text="error"></p>
        <p x-show="!loading && !error && !quote" class="text-stone-500">Choose a date to see the total price.</p>

        <template x-if="quote && !loading && !error">
            <dl class="space-y-1">
                <div class="flex justify-between"><dt x-text="quote.adults + ' adult(s), ' + quote.children + ' child(ren)'"></dt><dd x-text="fmt(quote.people_gross)"></dd></div>
                <div class="flex justify-between text-green-700" x-show="quote.tour_discount > 0"><dt>Tour discount</dt><dd x-text="'- ' + fmt(quote.tour_discount)"></dd></div>
                <div class="flex justify-between" x-show="quote.camp_amount > 0"><dt>Camp upgrade</dt><dd x-text="fmt(quote.camp_amount)"></dd></div>
                <div class="flex justify-between" x-show="quote.vehicle_amount > 0"><dt>Vehicle</dt><dd x-text="fmt(quote.vehicle_amount)"></dd></div>
                <template x-for="l in quote.addon_lines" :key="l.addon_id">
                    <div class="flex justify-between"><dt x-text="l.name + ' x ' + l.quantity"></dt><dd x-text="fmt(l.total)"></dd></div>
                </template>
                <div class="flex justify-between text-green-700" x-show="quote.coupon_discount > 0"><dt>Coupon</dt><dd x-text="'- ' + fmt(quote.coupon_discount)"></dd></div>
                <div class="flex justify-between" x-show="quote.tax > 0"><dt x-text="'Tax (' + quote.tax_percent + '%)'"></dt><dd x-text="fmt(quote.tax)"></dd></div>
                <div class="flex justify-between border-t border-stone-200 pt-2 text-base font-extrabold text-stone-900"><dt>Total</dt><dd x-text="fmt(quote.grand_total)"></dd></div>
            </dl>
        </template>
    </div>

    <button type="button" class="btn btn-primary mt-4 w-full disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canBook" @click="goCheckout()">Book Now</button>
    <p class="mt-2 text-center text-xs text-stone-500">No payment now. We confirm your booking first.</p>

    <a href="{{ whatsapp_url('Hello, I would like to book: '.$tour->title) }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-3 w-full">Ask on WhatsApp</a>
</div>

@push('scripts')
@verbatim
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingWidget', (cfg) => ({
        cfg,
        date: cfg.initial.date || '',
        adults: Number(cfg.initial.adults) || 2,
        children: Number(cfg.initial.children) || 0,
        campTypeId: cfg.initial.camp_type_id ? String(cfg.initial.camp_type_id) : '',
        vehicleId: cfg.initial.vehicle_id ? String(cfg.initial.vehicle_id) : '',
        addons: Array.isArray(cfg.initial.addons) ? {} : { ...cfg.initial.addons },
        coupon: cfg.initial.coupon_code || '',
        availability: Array.isArray(cfg.availability) ? {} : cfg.availability,
        quote: null,
        error: '',
        loading: false,
        seq: 0,
        timer: null,

        init() { if (this.date) this.refresh(); },

        get guests() { return Number(this.adults) + Number(this.children); },
        get entry() { return this.date ? this.availability[this.date] : null; },
        get dateOk() { return !!(this.entry && this.entry.open); },
        get canBook() { return !!this.quote && !this.error && !this.loading; },
        get openDates() { return Object.keys(this.availability).filter(d => this.availability[d].open).slice(0, 6); },
        get dateStatus() {
            if (!this.date) return 'Pick a date, or choose one of the next open dates.';
            if (!this.entry) return 'This date is not on sale yet. Please choose another date.';
            if (!this.entry.open) return 'Fully booked or closed on this date.';
            return this.entry.remaining + ' spots left';
        },

        fmt(n) {
            return cfg.symbol + ' ' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
        shortDate(d) {
            return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
        },

        toggleAddon(a) {
            if (this.addons[a.id]) { delete this.addons[a.id]; } else { this.addons[a.id] = 1; }
            this.schedule();
        },

        schedule() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.refresh(), 300);
        },

        payload() {
            const addons = {};
            Object.entries(this.addons).forEach(([id, qty]) => { if (Number(qty) > 0) addons[id] = Number(qty); });

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
            if (!this.dateOk) { this.error = this.dateStatus; return; }

            const id = ++this.seq;
            this.loading = true;

            try {
                const res = await fetch(cfg.quoteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.token },
                    body: JSON.stringify(this.payload()),
                });
                const data = await res.json().catch(() => ({}));
                if (id !== this.seq) return;

                if (!res.ok) {
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    this.error = data.message && !data.errors ? data.message : (first || 'Could not calculate the price.');
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