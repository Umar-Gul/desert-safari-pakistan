@extends('layouts.frontend')

@section('title', $name)
@section('meta_description', $item->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 155))

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden border-b border-stone-200 bg-gradient-to-br from-[#fbf3ea] via-white to-[#f6e7dc]">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#8B2014]/10 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-amber-300/20 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 pb-10 pt-6 sm:pb-14">
        <x-breadcrumbs :items="[[$kind, null], [$name, null]]" />

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-[#8B2014]/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#8B2014]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#8B2014]"></span>
                {{ $kind }}
            </span>
            @if (! $tours->isEmpty())
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-stone-700 ring-1 ring-stone-200">
                    Used on {{ $tours->count() }} {{ \Illuminate\Support\Str::plural('tour', $tours->count()) }}
                </span>
            @endif
        </div>

        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl lg:text-5xl">{{ $name }}</h1>

        @if ($facts !== [])
            <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($facts as $label => $value)
                    <div class="rounded-2xl bg-white/90 p-4 shadow-sm ring-1 ring-stone-200 backdrop-blur">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-500">{{ $label }}</dt>
                        <dd class="mt-1.5 text-lg font-extrabold leading-tight text-[#8B2014]">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-10 sm:py-14">

    {{-- About --}}
    @if ($item->description)
        <section class="mb-12 grid gap-8 lg:grid-cols-3 lg:items-start">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-stone-200 sm:p-8 lg:col-span-2">
                <h2 class="mb-4 text-xl font-bold text-stone-900">About {{ $name }}</h2>
                <div class="rich max-w-none text-stone-700">{!! \App\Support\Html::clean($item->description) !!}</div>
            </div>

            <aside class="rounded-3xl bg-gradient-to-br from-[#8B2014] to-[#6d180f] p-6 text-white shadow-lg sm:p-8 lg:sticky lg:top-24">
                <h3 class="text-lg font-extrabold">Have a question?</h3>
                <p class="mt-2 text-sm leading-relaxed text-white/80">Ask us about {{ $name }}, availability or pricing. We reply quickly.</p>
                <div class="mt-5 space-y-3">
                    <a href="{{ whatsapp_url('Hello, I have a question about: '.$name) }}" target="_blank" rel="noopener"
                       class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#8B2014] shadow-sm transition hover:bg-[#fbf3ea] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#8B2014]">Ask on WhatsApp</a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl border border-white/40 px-5 text-sm font-bold text-white transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Contact us</a>
                </div>
            </aside>
        </section>
    @endif

    {{-- Tours --}}
    <section>
        <div class="mb-6 flex items-end justify-between gap-4">
            <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 sm:text-3xl">{{ $toursHeading }}</h2>
            <a href="{{ route('tours.index') }}" class="hidden text-sm font-semibold text-[#8B2014] underline-offset-2 hover:underline sm:block">Browse all activities</a>
        </div>

        @if ($tours->isEmpty())
            <div class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-stone-200">
                <h3 class="text-lg font-bold text-stone-900">No tours are available right now</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-stone-600">Check back soon, or <a href="{{ route('contact') }}" class="font-semibold text-[#8B2014] underline underline-offset-2">contact us</a> and we will plan something for you.</p>
                <a href="{{ route('tours.index') }}" class="mt-6 inline-flex min-h-[46px] items-center justify-center rounded-xl bg-[#8B2014] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10]">Browse all activities</a>
            </div>
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