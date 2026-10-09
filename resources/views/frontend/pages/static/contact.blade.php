@extends('layouts.frontend')

@section('title', 'Contact Us')
@section('meta_description', 'Contact '.$site->site_name.' to plan or book your desert safari in Pakistan.')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-extrabold text-stone-900">Contact Us</h1>
    <p class="mb-8 text-stone-600">Questions about a tour, a group booking or an event? Send us a message and we will reply as soon as possible.</p>

    <div class="grid gap-10 lg:grid-cols-3">

        {{-- Contact details (from settings) --}}
        <aside class="space-y-5 rounded-2xl bg-white p-6 shadow-sm lg:col-span-1">
            @if ($site->address)
                <div><p class="text-xs font-semibold uppercase text-stone-500">Address</p><p>{{ $site->address }}</p></div>
            @endif
            @if ($site->phone)
                <div><p class="text-xs font-semibold uppercase text-stone-500">Phone</p>
                    <a href="{{ phone_href($site->phone) }}" class="text-amber-700">{{ $site->phone }}</a></div>
            @endif
            @if ($site->email)
                <div><p class="text-xs font-semibold uppercase text-stone-500">Email</p>
                    <a href="mailto:{{ $site->email }}" class="text-amber-700">{{ $site->email }}</a></div>
            @endif
            @if ($site->business_hours)
                <div><p class="text-xs font-semibold uppercase text-stone-500">Hours</p><p>{{ $site->business_hours }}</p></div>
            @endif
            @if ($site->whatsapp_number)
                <a href="{{ whatsapp_url('Hello, I have a question about your desert safari tours.') }}"
                   target="_blank" rel="noopener" class="btn btn-whatsapp w-full">Chat on WhatsApp</a>
            @endif
        </aside>

        {{-- Form layout is static; submission is wired to the enquiries table in Phase 5 --}}
        <section class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ url('/contact') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf

                {{-- Honeypot: real users never see or fill this --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label for="name" class="field-label">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="field">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="field-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="field">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="field-label">Phone / WhatsApp</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field" placeholder="+92 ...">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="subject" class="field-label">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject', request('subject')) }}" required class="field">
                    @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="message" class="field-label">Message</label>
                    <textarea id="message" name="message" rows="6" required class="field">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn btn-primary">Send message</button>
                </div>
            </form>
        </section>
    </div>

    @if ($site->map_embed_url)
        <div class="mt-10 overflow-hidden rounded-2xl">
            <iframe src="{{ $site->map_embed_url }}" width="100%" height="380" style="border:0" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
        </div>
    @endif
</div>
@endsection