@extends('layouts.frontend')

@section('title', 'Page not found')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="mx-auto max-w-xl px-4 py-24 text-center">
    <p class="text-6xl font-extrabold text-amber-600">404</p>
    <h1 class="mt-4 text-2xl font-bold text-stone-900">This dune has shifted</h1>
    <p class="mt-2 text-stone-600">We could not find the page you are looking for.</p>
    <div class="mt-6 flex justify-center gap-3">
        <a href="{{ route('home') }}" class="btn btn-primary">Back to home</a>
        <a href="{{ route('contact') }}" class="btn btn-outline">Contact us</a>
    </div>
</div>
@endsection