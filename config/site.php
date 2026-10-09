<?php

declare(strict_types=1);

return [

    /*
    | Fallback values used when the settings table is empty or unreachable
    | (fresh install, DB down on an error page, etc.).
    */
    'defaults' => [
        'site_name' => env('APP_NAME', 'Desert Safari Pakistan'),
        'tagline' => 'Cholistan, Thar & Thal desert adventures',
        'currency_code' => 'PKR',
        'currency_symbol' => 'Rs',
        'phone' => '',
        'whatsapp_number' => '',
        'email' => '',
        'address' => '',
        'tripadvisor_url' => '',
        'meta_title' => 'Desert Safari Pakistan',
        'meta_description' => 'Book guided desert safaris in Cholistan, Thar and Thal.',
        'hero_title' => 'Explore Pakistan\'s Golden Deserts',
        'hero_subtitle' => '',
        'hero_cta_text' => 'Browse Tours',
        'footer_text' => '',
        'trust_badges' => [],
    ],

        // Demo data (Pakistan catalogue). Set SEED_DEMO_DATA=false on a real production install.
    'seed_demo' => (bool) env('SEED_DEMO_DATA', true),
    'seed_images' => (bool) env('SEED_IMAGES', true),

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Super Admin'),
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe!12345'),
    ],
];