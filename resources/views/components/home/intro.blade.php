@props(['title' => null, 'html' => null])

@if (filled($html) || filled($title))
    <section class="mx-auto mt-14 max-w-4xl px-4 text-center">
        @if (filled($title))<h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">{{ $title }}</h2>@endif
        @if (filled($html))
            <div class="rich mt-4 text-left">{!! \App\Support\Html::clean($html) !!}</div>
        @endif
    </section>
@endif