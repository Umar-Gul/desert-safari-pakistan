<?php

declare(strict_types=1);

/*
| Settings screens. Each field is saved to the `settings` table under its key.
| Types: text, email, url, textarea, richtext (HTML, cleaned on save), image.
| Optional per field: required, max, hint, rules (array, overrides defaults).
*/

$phoneRule = ['nullable', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'];

return [
    'groups' => [
        'general' => [
            'label' => 'General',
            'fields' => [
                'site_name' => ['label' => 'Site name', 'type' => 'text', 'required' => true, 'max' => 120],
                'tagline' => ['label' => 'Tagline', 'type' => 'text', 'max' => 160],
                'logo' => ['label' => 'Logo', 'type' => 'image', 'hint' => 'PNG or WebP, up to 2 MB.'],
                'footer_text' => ['label' => 'Footer text', 'type' => 'textarea', 'max' => 500],
            ],
        ],

        'contact' => [
            'label' => 'Contact',
            'fields' => [
                'phone' => ['label' => 'Phone', 'type' => 'text', 'rules' => $phoneRule, 'hint' => 'Example: +92 300 1234567'],
                'whatsapp_number' => ['label' => 'WhatsApp number', 'type' => 'text', 'rules' => $phoneRule, 'hint' => 'International format, example: +92 300 1234567'],
                'email' => ['label' => 'Email', 'type' => 'email'],
                'address' => ['label' => 'Address', 'type' => 'text'],
                'business_hours' => ['label' => 'Business hours', 'type' => 'text', 'hint' => 'Example: Daily 8:00 AM - 10:00 PM'],
                'tripadvisor_url' => ['label' => 'TripAdvisor page URL', 'type' => 'url'],
            ],
        ],

        'social' => [
            'label' => 'Social links',
            'fields' => [
                'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url'],
                'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url'],
                'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url'],
                'tiktok_url' => ['label' => 'TikTok URL', 'type' => 'url'],
                'twitter_handle' => ['label' => 'X / Twitter handle', 'type' => 'text', 'max' => 50, 'hint' => 'Example: @yourhandle'],
            ],
        ],

        'hero' => [
            'label' => 'Home hero',
            'fields' => [
                'hero_eyebrow' => ['label' => 'Small line above the title', 'type' => 'text', 'max' => 80, 'hint' => 'Example: From dunes to dreams'],
                'hero_title' => ['label' => 'Hero title', 'type' => 'text', 'max' => 160],
                'hero_subtitle' => ['label' => 'Hero subtitle', 'type' => 'textarea', 'max' => 300],
                'hero_cta_text' => ['label' => 'Main button text', 'type' => 'text', 'max' => 40, 'hint' => 'Leave empty for "Explore all tours".'],
                'hero_image' => ['label' => 'Hero image', 'type' => 'image', 'hint' => 'Wide image, at least 1600 px. Up to 2 MB.'],
            ],
        ],

        'home' => [
            'label' => 'Home intro text',
            'fields' => [
                'home_intro_title' => ['label' => 'Intro heading', 'type' => 'text', 'max' => 160],
                'home_intro_html' => [
                    'label' => 'Intro text',
                    'type' => 'richtext',
                    'hint' => 'Basic HTML allowed: p, h2, h3, ul, li, strong, em, a. Anything else is removed on save.',
                ],
            ],
        ],

        'seo' => [
            'label' => 'Default SEO',
            'fields' => [
                'default_meta_title' => ['label' => 'Default meta title', 'type' => 'text', 'max' => 70],
                'default_meta_description' => ['label' => 'Default meta description', 'type' => 'textarea', 'max' => 320],
                'default_og_image' => ['label' => 'Default share image', 'type' => 'image', 'hint' => '1200 x 630 px recommended.'],
            ],
        ],
    ],
];