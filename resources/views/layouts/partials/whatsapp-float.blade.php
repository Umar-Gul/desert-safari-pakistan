@if ($site->whatsapp_number)
    <a href="{{ whatsapp_url('Hello, I would like to book a desert safari.') }}"
       target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
       class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2z"/>
        </svg>
        <span class="hidden sm:inline">WhatsApp</span>
    </a>
@endif