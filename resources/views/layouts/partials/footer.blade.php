<footer class="mt-16 bg-stone-900 text-stone-300">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-4">
        <div>
            <h3 class="mb-3 text-lg font-bold text-white">{{ $site->site_name }}</h3>
            <p class="text-sm leading-relaxed">{{ $site->footer_text }}</p>
            <div class="mt-4 flex gap-3 text-sm">
                @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok'] as $key => $label)
                    @if ($site->{$key})
                        <a href="{{ $site->{$key} }}" target="_blank" rel="noopener"
                            class="hover:text-white">{{ $label }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white">Explore</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ url('/tours') }}" class="hover:text-white">All Activities</a></li>
                @foreach (($navCategories ?? collect())->take(6) as $cat)
                    <li><a href="{{ route('categories.show', $cat->slug) }}"
                            class="hover:text-white">{{ $cat->name }}</a></li>
                @endforeach
                <li><a href="{{ url('/blog') }}" class="hover:text-white">Blog</a></li>
                <li><a href="{{ url('/gallery') }}" class="hover:text-white">Gallery</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white">Policies</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('terms') }}" class="hover:text-white">Terms &amp; Conditions</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('cookies') }}" class="hover:text-white">Cookie Policy</a></li>
                <li><a href="{{ route('refund') }}" class="hover:text-white">Cancellation &amp; Refunds</a></li>
            </ul>
        </div>

        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-white">Contact</h4>
            <ul class="space-y-2 text-sm">
                @if ($site->address)
                    <li>{{ $site->address }}</li>
                @endif
                @if ($site->phone)
                    <li><a href="{{ phone_href($site->phone) }}" class="hover:text-white">{{ $site->phone }}</a></li>
                @endif
                @if ($site->email)
                    <li><a href="mailto:{{ $site->email }}" class="hover:text-white">{{ $site->email }}</a></li>
                @endif
                @if ($site->business_hours)
                    <li>{{ $site->business_hours }}</li>
                @endif
                @if ($site->tripadvisor_url)
                    <li><a href="{{ $site->tripadvisor_url }}" target="_blank" rel="noopener"
                            class="hover:text-white">Reviews on TripAdvisor</a></li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-stone-800">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs md:flex-row">
            <p>&copy; {{ date('Y') }} {{ $site->site_name }}. All rights reserved.</p>
            <p>Secure payments: Bank transfer &middot; JazzCash &middot; Easypaisa &middot; Card &middot; Cash on
                arrival</p>
        </div>
    </div>
</footer>
