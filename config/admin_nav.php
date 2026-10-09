<?php

declare(strict_types=1);

/*
| Sidebar definition. An item is shown only if its route exists
| (so it appears automatically when a later phase registers it)
| and the user has the permission (super_admin passes via Gate::before).
*/
return [
    'Overview' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null],
    ],
    'Sales' => [
        ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'permission' => 'bookings.view'],
        ['label' => 'Availability', 'route' => 'admin.availabilities.index', 'permission' => 'availability.view'],
        ['label' => 'Payments', 'route' => 'admin.payments.index', 'permission' => 'payments.view'],
        ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'permission' => 'coupons.view'],
    ],
    'Catalogue' => [
        ['label' => 'Tours', 'route' => 'admin.tours.index', 'permission' => 'tours.view'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'permission' => 'categories.view'],
        ['label' => 'Deserts', 'route' => 'admin.deserts.index', 'permission' => 'deserts.view'],
        ['label' => 'Add-ons', 'route' => 'admin.addons.index', 'permission' => 'addons.view'],
        ['label' => 'Camp types', 'route' => 'admin.camp-types.index', 'permission' => 'camp_types.view'],
        ['label' => 'Vehicles', 'route' => 'admin.vehicles.index', 'permission' => 'vehicles.view'],
        ['label' => 'Facilities', 'route' => 'admin.facilities.index', 'permission' => 'facilities.view'],
        ['label' => 'Highlights', 'route' => 'admin.highlights.index', 'permission' => 'highlights.view'],
        ['label' => 'Event packages', 'route' => 'admin.event-packages.index', 'permission' => 'event_packages.view'],
    ],
    'Content' => [
        ['label' => 'Blog', 'route' => 'admin.blogs.index', 'permission' => 'blogs.view'],
        ['label' => 'Gallery', 'route' => 'admin.gallery.index', 'permission' => 'gallery.view'],
        ['label' => 'FAQs', 'route' => 'admin.faqs.index', 'permission' => 'faqs.view'],
    ],
    'Customers' => [
        ['label' => 'Enquiries', 'route' => 'admin.enquiries.index', 'permission' => 'enquiries.view'],
        ['label' => 'Questions', 'route' => 'admin.questions.index', 'permission' => 'questions.view'],
        ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'permission' => 'reviews.view'],
    ],
    'System' => [
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.view'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.view'],
    ],
];