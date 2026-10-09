<div x-data="{ show: false }"
     x-init="show = !localStorage.getItem('cookie_consent')"
     x-show="show" x-cloak
     class="fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white p-4 shadow-lg">
    <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
        <p class="text-sm text-stone-700">
            We use cookies to keep the site working and to understand how it is used.
            See our <a href="{{ route('cookies') }}" class="text-amber-700 underline">Cookie Policy</a>.
        </p>
        <div class="flex gap-2">
            <button type="button" class="btn btn-outline"
                    @click="localStorage.setItem('cookie_consent','essential'); show = false">Essential only</button>
            <button type="button" class="btn btn-primary"
                    @click="localStorage.setItem('cookie_consent','all'); show = false">Accept all</button>
        </div>
    </div>
</div>