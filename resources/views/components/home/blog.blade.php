@props(['posts'])

@if ($posts->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">From the blog</h2>
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">All articles &rarr;</a>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($posts as $post)
                <x-home.blog-card :post="$post" />
            @endforeach
        </div>
    </section>
@endif