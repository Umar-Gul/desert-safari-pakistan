@props(['model', 'conversion' => 'card', 'eager' => false])

@php $media = $model->getFirstMedia('cover'); @endphp

@if ($media)
    {{ $media->img($conversion, [
        'class' => $attributes->get('class'),
        'alt' => $model->coverAlt(),
        'loading' => $eager ? 'eager' : 'lazy',
        'decoding' => 'async',
    ]) }}
@else
    <div {{ $attributes->class(['bg-gradient-to-br from-amber-200 via-orange-200 to-amber-400']) }} role="img" aria-label="{{ $model->name ?? $model->title }}"></div>
@endif