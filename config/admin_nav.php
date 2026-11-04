<?php

declare(strict_types=1);

/*
| Sidebar definition. An item is shown only if its route exists
| (so it appears automatically when a later phase registers it)
| and the user has the permission (super_admin passes via Gate::before).
|
| 'icon' => icon name used by <x-admin.sidebar>
| 'more' => true  hides the item behind "Show more" (the active page is always shown)
*/
return [
    'Overview' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null, 'icon' => 'home'],
    ],
    'Sales' => [
        ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'permission' => 'bookings.view', 'icon' => 'ticket'],
        ['label' => 'Dates & Seats', 'route' => 'admin.availabilities.index', 'permission' => 'availability.view', 'icon' => 'calendar'],
        ['label' => 'Payments', 'route' => 'admin.payments.index', 'permission' => 'payments.view', 'icon' => 'card'],
        ['label' => 'Discount Codes', 'route' => 'admin.coupons.index', 'permission' => 'coupons.view', 'icon' => 'tag'],
    ],
    'Catalogue' => [
        ['label' => 'Tours', 'route' => 'admin.tours.index', 'permission' => 'tours.view', 'icon' => 'map'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'permission' => 'categories.view', 'icon' => 'grid'],
        ['label' => 'Deserts', 'route' => 'admin.deserts.index', 'permission' => 'deserts.view', 'icon' => 'sun'],
        ['label' => 'Extras', 'route' => 'admin.addons.index', 'permission' => 'addons.view', 'icon' => 'plus'],
        ['label' => 'Camp Styles', 'route' => 'admin.camp-types.index', 'permission' => 'camp_types.view', 'icon' => 'tent'],
        ['label' => 'Vehicles', 'route' => 'admin.vehicles.index', 'permission' => 'vehicles.view', 'icon' => 'truck', 'more' => true],
        ['label' => 'Facilities', 'route' => 'admin.facilities.index', 'permission' => 'facilities.view', 'icon' => 'check', 'more' => true],
        ['label' => 'Main Attractions', 'route' => 'admin.highlights.index', 'permission' => 'highlights.view', 'icon' => 'sparkles', 'more' => true],
        ['label' => 'Event Packages', 'route' => 'admin.event-packages.index', 'permission' => 'event_packages.view', 'icon' => 'gift', 'more' => true],
    ],
    'Content' => [
        ['label' => 'Blog', 'route' => 'admin.blogs.index', 'permission' => 'blogs.view', 'icon' => 'news'],
        ['label' => 'Gallery', 'route' => 'admin.gallery.index', 'permission' => 'gallery.view', 'icon' => 'photo', 'more' => true],
        ['label' => 'FAQs', 'route' => 'admin.faqs.index', 'permission' => 'faqs.view', 'icon' => 'help', 'more' => true],
    ],
    'Customers' => [
        ['label' => 'Enquiries', 'route' => 'admin.enquiries.index', 'permission' => 'enquiries.view', 'icon' => 'mail'],
        ['label' => 'Questions', 'route' => 'admin.questions.index', 'permission' => 'questions.view', 'icon' => 'chat'],
        ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'permission' => 'reviews.view', 'icon' => 'star'],
    ],
    'System' => [
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.view', 'icon' => 'cog'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'users'],
    ],
];