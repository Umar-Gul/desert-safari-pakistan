@props(['badges'])

<section class="mx-auto -mt-8 max-w-7xl px-4">
    <div class="grid gap-4 rounded-2xl bg-white p-6 shadow-lg sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($badges as $badge)
            @php
                $isArray = is_array($badge);
                $title = $isArray ? ($badge['title'] ?? '') : $badge->title;
                $subtitle = $isArray ? ($badge['text'] ?? '') : $badge->subtitle;
            @endphp
            <div class="flex items-start gap-3">
                @unless ($isArray)
                    @if ($badge->getFirstMedia('cover'))
                        <x-cover :model="$badge" conversion="thumb" class="h-10 w-10 shrink-0 rounded-full object-cover" />
                    @endif
                @endunless
                <div>
                    <p class="font-semibold text-stone-900">{{ $title }}</p>
                    @if ($subtitle)<p class="mt-0.5 text-sm text-stone-600">{{ $subtitle }}</p>@endif
                </div>
            </div>
        @endforeach
    </div>
</section>