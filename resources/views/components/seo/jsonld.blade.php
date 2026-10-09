@props(['data'])
<script type="application/ld+json">{!! app(\App\Services\SeoService::class)->encode($data) !!}</script>