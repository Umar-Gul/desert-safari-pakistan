@php
    $user = auth()->user();
    $m = 'App\\Models\\';

    // [label, route name, permission]. Permission: null, a gate name, or [ability, model class].
    $sections = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', null],
        ],
        'Operations' => [
            ['Bookings', 'admin.bookings.index', ['viewAny', $m.'Booking']],
            ['Availability', 'admin.availabilities.index', ['viewAny', $m.'Availability']],
            ['Payments', 'admin.payments.index', ['viewAny', $m.'Payment']],
            ['Coupons', 'admin.coupons.index', ['viewAny', $m.'Coupon']],
        ],
        'Catalogue' => [
            ['Tours', 'admin.tours.index', ['viewAny', $m.'Tour']],
            ['Categories', 'admin.categories.index', ['viewAny', $m.'Category']],
            ['Deserts', 'admin.deserts.index', ['viewAny', $m.'Desert']],
            ['Add-ons', 'admin.addons.index', ['viewAny', $m.'Addon']],
            ['Camp types', 'admin.camp-types.index', ['viewAny', $m.'CampType']],
            ['Vehicles', 'admin.vehicles.index', ['viewAny', $m.'Vehicle']],
            ['Facilities', 'admin.facilities.index', ['viewAny', $m.'Facility']],
            ['Highlights', 'admin.highlights.index', ['viewAny', $m.'Highlight']],
        ],
        'Home page' => [
            ['Trust badges', 'admin.trust-badges.index', ['viewAny', $m.'TrustBadge']],
            ['Quick links', 'admin.quick-links.index', ['viewAny', $m.'QuickLink']],
            ['Event packages', 'admin.event-packages.index', ['viewAny', $m.'EventPackage']],
            ['Gallery', 'admin.gallery-items.index', ['viewAny', $m.'GalleryItem']],
            ['FAQs', 'admin.faqs.index', ['viewAny', $m.'Faq']],
        ],
        'Blog' => [
            ['Posts', 'admin.blogs.index', ['viewAny', $m.'Blog']],
            ['Categories', 'admin.blog-categories.index', ['viewAny', $m.'BlogCategory']],
        ],
        'Customers' => [
            ['Reviews', 'admin.reviews.index', ['viewAny', $m.'Review']],
            ['Questions', 'admin.questions.index', ['viewAny', $m.'Question']],
            ['Enquiries', 'admin.enquiries.index', ['viewAny', $m.'Enquiry']],
        ],
        'System' => [
            ['Settings', 'admin.settings.edit', 'manage-settings'],
            ['Users', 'admin.users.index', 'manage-users'],
        ],
    ];

    // Keep only items whose route exists and that this user may see.
    $visible = [];
    foreach ($sections as $heading => $items) {
        foreach ($items as [$label, $route, $permission]) {
            if (! Route::has($route)) {
                continue;
            }

            $allowed = $permission === null
                || (is_array($permission) ? $user->can(...$permission) : $user->can($permission));

            if (! $allowed) {
                continue;
            }

            $active = \Illuminate\Support\Str::endsWith($route, '.dashboard')
                ? request()->routeIs($route)
                : request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.').'.*');

            $visible[$heading][] = compact('label', 'route', 'active');
        }
    }
@endphp

<div class="px-5 py-5">
    <a href="{{ route('admin.dashboard') }}" class="block text-lg font-bold text-white">
        {{ \App\Models\Setting::get('site_name', config('app.name')) }}
    </a>
    <span class="text-xs uppercase tracking-wide text-stone-500">Admin panel</span>
</div>

<nav class="space-y-6 px-3 pb-8 text-sm">
    @foreach ($visible as $heading => $items)
        <div>
            <p class="mb-1 px-2 text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $heading }}</p>
            <ul class="space-y-0.5">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                           class="block rounded-md px-3 py-2 {{ $item['active'] ? 'bg-amber-600 font-medium text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>