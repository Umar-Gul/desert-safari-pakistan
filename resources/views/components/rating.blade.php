@props(['value' => 0, 'count' => 0])

@if ((int) $count > 0)
    <span {{ $attributes->class(['inline-flex items-center gap-1 text-sm']) }}>
        <span class="text-amber-500" aria-hidden="true">&#9733;</span>
        <span class="font-semibold text-stone-900">{{ number_format((float) $value, 1) }}</span>
        <span class="text-stone-500">({{ $count }})</span>
    </span>
@else
    <span {{ $attributes->class(['text-xs text-stone-400']) }}>No reviews yet</span>
@endif