@props(['tour', 'size' => 'md'])

<div {{ $attributes->class(['flex flex-wrap items-baseline gap-x-2']) }}>
    @if ($tour->has_discount)
        <span class="text-sm text-stone-400 line-through">{{ money($tour->base_price_adult) }}</span>
    @endif
    <span class="{{ $size === 'lg' ? 'text-3xl' : 'text-xl' }} font-extrabold text-stone-900">{{ money($tour->final_price_adult) }}</span>
    <span class="text-xs text-stone-500">{{ $tour->price_label }}</span>
</div>