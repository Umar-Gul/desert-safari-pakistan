<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->defaults() as [$group, $key, $value, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'type' => $type,
                    'value' => $type === 'json' ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                ]
            );
        }

        Setting::flushCache();
    }

    /** @return list<array{0:string,1:string,2:mixed,3:string}> */
    private function defaults(): array
    {
        return [
            // General
            ['general', 'site_name', 'Desert Safari Pakistan', 'string'],
            ['general', 'tagline', 'Cholistan, Thar & Thal desert adventures', 'string'],
            ['general', 'logo', null, 'image'],
            ['general', 'favicon', null, 'image'],
            ['general', 'footer_text', 'Family-friendly, locally guided desert experiences across Pakistan.', 'text'],

            // Currency
            ['currency', 'currency_code', 'PKR', 'string'],
            ['currency', 'currency_symbol', 'Rs', 'string'],
            ['currency', 'show_usd', '0', 'bool'],
            ['currency', 'usd_rate', '0.0036', 'string'],

            // Contact
            ['contact', 'phone', '+92 300 0000000', 'string'],
            ['contact', 'whatsapp_number', '923000000000', 'string'],
            ['contact', 'email', 'info@example.com', 'string'],
            ['contact', 'address', 'Bahawalpur, Punjab, Pakistan', 'string'],
            ['contact', 'business_hours', 'Daily, 8:00 AM to 10:00 PM (PKT)', 'string'],
            ['contact', 'tripadvisor_url', '', 'string'],
            ['contact', 'map_embed_url', '', 'string'],

            // Social
            ['social', 'facebook_url', '', 'string'],
            ['social', 'instagram_url', '', 'string'],
            ['social', 'youtube_url', '', 'string'],
            ['social', 'tiktok_url', '', 'string'],

            // Hero
            ['hero', 'hero_title', "Explore Pakistan's Golden Deserts", 'string'],
            ['hero', 'hero_subtitle', 'Guided safaris in Cholistan, Thar and Thal. Camel rides, dune drives, folk music nights and starlit camps.', 'text'],
            ['hero', 'hero_image', null, 'image'],
            ['hero', 'hero_cta_text', 'Browse Tours', 'string'],

            // Trust badges
            ['trust', 'trust_badges', [
                ['title' => 'Local Expert Guides', 'text' => 'Experienced drivers and guides from desert communities'],
                ['title' => 'Family Friendly', 'text' => 'Safe, respectful experiences for all ages'],
                ['title' => 'Flexible Payments', 'text' => 'Bank transfer, JazzCash, Easypaisa, card or cash'],
                ['title' => 'Free Cancellation', 'text' => 'Cancel up to 24 hours before your trip'],
            ], 'json'],

            // SEO defaults
            ['seo', 'meta_title', 'Desert Safari Pakistan | Cholistan, Thar & Thal Tours', 'string'],
            ['seo', 'meta_description', 'Book guided desert safaris in Cholistan, Thar and Thal. Camel rides, jeep safaris, overnight camping, folk music and halal desert dinners.', 'text'],
            ['seo', 'default_og_image', '', 'string'],
            ['seo', 'google_analytics_id', '', 'string'],

            // Payments
            ['payment', 'payment_methods', ['bank_transfer', 'jazzcash', 'easypaisa', 'card', 'cash'], 'json'],
            ['payment', 'bank_details', "Account title: \nBank: \nIBAN: \nJazzCash / Easypaisa: ", 'text'],

            // Email
            ['email', 'mail_from_name', 'Desert Safari Pakistan', 'string'],
            ['email', 'admin_notification_email', 'info@example.com', 'string'],

            // Cancellation
            ['cancellation', 'free_cancellation_hours', '24', 'int'],
            ['cancellation', 'late_cancellation_fee_percent', '25', 'int'],

            // Booking rules
            ['booking', 'booking_cutoff_hours', '12', 'int'],
            ['booking', 'tax_percent', '0', 'int'],
            ['booking', 'pickup_cities', ['Bahawalpur', 'Multan', 'Karachi', 'Lahore', 'Hyderabad', 'Islamabad'], 'json'],
        ];
    }
}