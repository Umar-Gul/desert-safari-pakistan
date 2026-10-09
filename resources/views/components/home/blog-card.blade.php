@props(['post'])

<article class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:shadow-md">
    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] overflow-hidden bg-stone-100">
        <x-cover :model="$post" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
    </a>
    <div class="p-5">
        <p class="text-xs text-stone-500">
            {{ $post->published_at?->format('d M Y') }}
            @if ($post->category) &middot; <span class="text-amber-700">{{ $post->category->name }}</span>@endif
        </p>
        <h3 class="mt-1 text-lg font-bold leading-snug text-stone-900">
            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-amber-700">{{ $post->title }}</a>
        </h3>
        <p class="mt-2 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit((string) $post->excerpt, 120) }}</p>
    </div>
</article>