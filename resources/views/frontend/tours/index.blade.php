@extends('layouts.frontend')

@section('title', 'All Desert Safari Tours')
@section('meta_description', 'Browse all desert safari tours, camel rides, jeep safaris and overnight camps in Cholistan, Thar and Thal.')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <x-breadcrumbs :items="[['All activities', null]]" />
    <h1 class="mb-6 mt-3 text-3xl font-extrabold text-stone-900">All activities</h1>

    @include('frontend.partials.tour-list', ['action' => route('tours.index')])
</div>
@endsection