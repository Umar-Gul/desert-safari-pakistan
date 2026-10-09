@props(['reviews'])

@if ($reviews->isNotEmpty())
    <section class="mt-16 bg-amber-50 py-14">
        <div class="mx-auto max-w-7xl px-4">
            <h2 class="mb-8 text-center text-2xl font-extrabold text-stone-900 sm:text-3xl">What our guests say</h2>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($reviews as $review)
                    <figure class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-amber-100">
                        <p class="text-amber-500" aria-label="{{ $review->rating }} out of 5">{{ str_repeat('★', $review->rating) }}<span class="text-stone-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                        @if ($review->title)<p class="mt-2 font-semibold text-stone-900">{{ $review->title }}</p>@endif
                        <blockquote class="mt-1 text-sm text-stone-700">{{ \Illuminate\Support\Str::limit($review->comment, 200) }}</blockquote>
                        <figcaption class="mt-3 text-xs text-stone-500">
                            {{ $review->name }}{{ $review->country ? ', '.$review->country : '' }}
                            @if ($review->tour) &middot; <a href="{{ route('tours.show', $review->tour->slug) }}" class="text-amber-700 hover:underline">{{ $review->tour->title }}</a>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif