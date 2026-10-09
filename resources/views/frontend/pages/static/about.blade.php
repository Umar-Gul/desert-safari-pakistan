@extends('layouts.frontend')

@section('title', 'About Us')
@section('meta_description', 'Learn about our locally guided desert safaris across Cholistan, Thar and Thal in Pakistan.')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <h1 class="mb-6 text-3xl font-extrabold text-stone-900">About {{ $site->site_name }}</h1>

    <div class="legal">
        <p>
            {{ $site->site_name }} was founded to share the deserts of Pakistan with travellers, families,
            schools and companies in a safe, respectful and memorable way. From the dunes of Cholistan and
            the villages of Thar to the sandy plains of Thal, our guides grew up in these landscapes and
            know them better than anyone.
        </p>

        <h2>What we do</h2>
        <p>
            We organise day safaris, sunset trips, overnight camps, camel and jeep experiences, and private
            or group events. Every trip is planned around safety, comfort and authentic local culture,
            including folk music, traditional food and the hospitality desert communities are known for.
        </p>

        <h2>Our approach</h2>
        <ul>
            <li>Local guides and drivers who know the terrain and the weather.</li>
            <li>Family-friendly experiences with a prayer area and halal food at our camps.</li>
            <li>Clear prices with no hidden charges, confirmed in writing before your trip.</li>
            <li>Respect for local communities, wildlife and the desert environment.</li>
        </ul>

        <h2>Plan your trip</h2>
        <p>
            Browse our <a href="{{ url('/tours') }}">tours</a> or <a href="{{ route('contact') }}">contact us</a>
            and we will help you choose the right experience for your group.
        </p>
    </div>
</div>
@endsection