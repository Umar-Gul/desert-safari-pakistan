@props(['items'])

@php
    $listItems = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')]];

    foreach (array_values($items) as $i => [$label, $url]) {
        $entry = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => $label];

        if ($url) {
            $entry['item'] = $url;
        }

        $listItems[] = $entry;
    }

    $breadcrumbData = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $listItems];
@endphp

@push('jsonld')
    <x-seo.jsonld :data="$breadcrumbData" />
@endpush

<nav aria-label="Breadcrumb" class="text-sm text-stone-500">
    <ol class="flex flex-wrap items-center gap-1">
        <li><a href="{{ route('home') }}" class="hover:text-amber-700">Home</a></li>
        @foreach ($items as [$label, $url])
            <li aria-hidden="true">/</li>
            <li>
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-amber-700">{{ $label }}</a>
                @else
                    <span class="text-stone-700" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>