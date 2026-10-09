@props(['color' => 'stone', 'label'])

@php
    $map = [
        'amber' => 'bg-amber-100 text-amber-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'stone' => 'bg-stone-200 text-stone-700',
    ];
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($map[$color] ?? $map['stone'])]) }}>{{ $label }}</span>
