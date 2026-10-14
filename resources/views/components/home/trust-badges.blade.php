@props(['badges'])

<style>
    .trust-badge-top-rated {
        position: relative;
        top: 0;
        transform: none;
        animation: none;
        transition: none;
    }
</style>

<section class="relative z-10 mx-auto -mt-6 max-w-7xl px-4">
    <div class="grid gap-3 rounded-2xl border border-white/30 bg-white/15 px-4 py-3 shadow-lg backdrop-blur-xl sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($badges as $badge)
            @php
                $isArray = is_array($badge);
                $title = $isArray ? ($badge['title'] ?? '') : $badge->title;
                $subtitle = $isArray ? ($badge['text'] ?? '') : $badge->subtitle;
            @endphp

            <div
                class="flex items-center gap-3 {{ trim($title) === 'Top Rated Experience' ? 'trust-badge-top-rated' : '' }}"
                @if (trim($title) === 'Top Rated Experience') data-no-reveal @endif
            >
                @unless ($isArray)
                    @if ($badge->getFirstMedia('cover'))
                        <x-cover
                            :model="$badge"
                            conversion="thumb"
                            class="h-8 w-8 shrink-0 rounded-full border border-white/30 object-cover"
                        />
                    @endif
                @endunless

                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white drop-shadow-md">
                        {{ $title }}
                    </p>

                    @if ($subtitle)
                        <p class="mt-0.5 text-xs text-white/85 drop-shadow-sm">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>