<div class="hidden bg-stone-900 text-xs text-stone-200 md:block">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2">
        <div class="flex items-center gap-5">
            @if ($site->phone)
                <a href="{{ phone_href($site->phone) }}" class="hover:text-white">Call: {{ $site->phone }}</a>
            @endif
            @if ($site->whatsapp_number)
                <a href="{{ whatsapp_url('Hello, I would like to know more about your desert safari tours.') }}"
                   target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a>
            @endif
            @if ($site->email)
                <a href="mailto:{{ $site->email }}" class="hover:text-white">{{ $site->email }}</a>
            @endif
        </div>
        <div class="flex items-center gap-5">
            @if ($site->tripadvisor_url)
                <a href="{{ $site->tripadvisor_url }}" target="_blank" rel="noopener" class="hover:text-white">TripAdvisor</a>
            @endif
            <span>{{ $site->business_hours }}</span>
        </div>
    </div>
</div>