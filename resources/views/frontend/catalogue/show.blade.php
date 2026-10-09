@extends('layouts.frontend')

@section('title', $name)
@section('meta_description', $item->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 155))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <x-breadcrumbs :items="[[$kind, null], [$name, null]]" />

    <header class="mb-8 mt-3">
        <h1 class="text-3xl font-extrabold text-stone-900 sm:text-4xl">{{ $name }}</h1>
        @if ($item->description)
            <div class="rich mt-4 max-w-3xl">{!! \App\Support\Html::clean($item->description) !!}</div>
        @endif
    </header>

    @if ($facts !== [])
        <dl class="mb-8 flex flex-wrap gap-4">
            @foreach ($facts as $label => $value)
                <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-stone-200">
                    <dt class="text-xs font-medium uppercase tracking-wide text-stone-500">{{ $label }}</dt>
                    <dd class="mt-1 font-semibold text-stone-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    <section>
        <h2 class="mb-5 text-2xl font-bold text-stone-900">{{ $toursHeading }}</h2>
        @if ($tours->isEmpty())
            <p class="text-stone-600">No tours are available right now.</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($tours as $tour)
                    <x-tour-card :tour="$tour" />
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection