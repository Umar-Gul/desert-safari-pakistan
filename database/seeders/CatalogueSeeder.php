<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdjustmentType;
use App\Enums\QuickLinkGroup;
use App\Models\Addon;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\EventPackage;
use App\Models\Facility;
use App\Models\Highlight;
use App\Models\QuickLink;
use App\Models\TrustBadge;
use App\Models\Vehicle;
use App\Support\CacheKeys;
use Database\Seeders\Support\SeedImages;
use Illuminate\Database\Seeder;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $this->categories();
        $this->deserts();
        $this->campTypes();
        $this->vehicles();
        $this->facilities();
        $this->highlights();
        $this->addons();
        $this->trustBadgesAndLinks();
        $this->eventPackages();

        CacheKeys::forgetCatalogue();
    }

    private function categories(): void
    {
        $rows = [
            ['cholistan-desert-safari', 'Cholistan Desert Safari', 'Jeep drives, camel rides and camp nights in the golden dunes around Bahawalpur and Derawar.', true, true],
            ['thar-desert-safari', 'Thar Desert Safari', 'Villages, peacocks and rolling dunes of Tharparkar, from Mithi to Islamkot.', true, true],
            ['thal-desert-safari', 'Thal Desert Safari', 'Easy, family-friendly safaris on the sandy plains of Thal in Punjab.', true, true],
            ['camel-safari', 'Camel Safari', 'Slow, scenic camel rides at sunrise, sunset and under the stars.', true, false],
            ['jeep-safari-dune-bashing', 'Jeep Safari & Dune Bashing', 'Open-jeep drives and dune bashing with experienced desert drivers.', true, false],
            ['overnight-desert-camping', 'Overnight Desert Camping', 'Sleep under the stars in a comfortable camp with dinner, music and breakfast.', true, false],
            ['quad-biking-adventure', 'Quad Biking & Adventure', 'Short, thrilling quad rides across the dunes with safety gear provided.', false, false],
            ['sightseeing-heritage', 'Sightseeing & Heritage', 'Derawar Fort, Mithi, Islamkot, Kharan and the stories behind them.', false, false],
        ];

        foreach ($rows as $i => [$slug, $name, $description, $menu, $quick]) {
            $category = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'show_in_menu' => $menu || $quick,
                'is_quick_link' => $quick,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($category, 'category-'.$slug, $name);
        }
    }

    private function deserts(): void
    {
        $rows = [
            ['cholistan-desert', 'Cholistan Desert', 'Bahawalpur, Punjab', 'Known locally as Rohi, Cholistan is a vast sandy desert with ancient forts, seasonal lakes and star-filled night skies.'],
            ['thar-desert', 'Thar Desert', 'Tharparkar, Sindh', 'A living desert of colourful villages, folk music and craftsmanship, centred around Mithi and Islamkot.'],
            ['thal-desert', 'Thal Desert', 'Bhakkar and Layyah, Punjab', 'A flatter, greener desert close to the Indus, ideal for relaxed family safaris and sunset drives.'],
            ['kharan-desert', 'Kharan Desert', 'Kharan, Balochistan', 'A remote desert of high red dunes and wide horizons, best explored on a guided multi-day expedition.'],
        ];

        foreach ($rows as $i => [$slug, $name, $location, $description]) {
            $desert = Desert::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'location' => $location,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($desert, 'desert-'.$slug, $name);
        }
    }

    private function campTypes(): void
    {
        $rows = [
            ['standard-family-camp', 'Standard Family Camp', 'Clean shared tents with seating, rugs and a traditional dinner area. Our most popular choice.', AdjustmentType::Fixed, 0],
            ['premium-camp', 'Premium Camp', 'Larger tents with proper beds, lighting and a private seating area.', AdjustmentType::Fixed, 1500],
            ['vip-majlis-camp', 'VIP Majlis Camp', 'A traditional majlis-style tent with cushioned seating, a dedicated host and private dining.', AdjustmentType::Fixed, 3000],
            ['private-camp', 'Private Camp', 'The whole camp reserved for your family or group, with your own music and dinner setup.', AdjustmentType::Percent, 25],
        ];

        foreach ($rows as $i => [$slug, $name, $description, $type, $value]) {
            $camp = CampType::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'price_modifier_type' => $type->value,
                'price_modifier_value' => $value,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($camp, 'camp-'.$slug, $name);
        }
    }

    private function vehicles(): void
    {
        $rows = [
            ['toyota-land-cruiser', 'Toyota Land Cruiser', 6, 6000, 'Spacious 4x4 with air conditioning, ideal for families and long desert routes.'],
            ['toyota-prado', 'Toyota Prado', 5, 5000, 'Comfortable 4x4 for small groups, with plenty of luggage space.'],
            ['toyota-hilux-double-cabin', 'Toyota Hilux Double Cabin', 5, 3500, 'Rugged pickup built for sand tracks and rough desert roads.'],
            ['open-safari-jeep', 'Open Safari Jeep', 4, 4500, 'Open-top jeep for the best views and photos on dune drives.'],
            ['toyota-coaster-bus', 'Toyota Coaster', 20, 12000, 'Air-conditioned 20-seat coaster for school trips, corporate groups and large families.'],
        ];

        foreach ($rows as $i => [$slug, $name, $capacity, $modifier, $description]) {
            $vehicle = Vehicle::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'capacity' => $capacity,
                'price_modifier' => $modifier,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($vehicle, 'vehicle-'.$slug, $name);
        }
    }

    private function facilities(): void
    {
        $rows = [
            ['prayer-area', 'Prayer Area', 'A clean, covered prayer area with mats at every camp.'],
            ['clean-restrooms', 'Clean Restrooms', 'Separate restrooms for men and women with running water.'],
            ['secure-parking', 'Secure Parking', 'Safe parking for your own vehicle at the meeting point.'],
            ['first-aid-point', 'First Aid Point', 'A trained team member and a stocked first aid kit on every trip.'],
            ['souvenir-grocery-shop', 'Souvenir and Grocery Shop', 'Local handicrafts, snacks, water and essentials at the camp shop.'],
            ['wheelchair-friendly-access', 'Wheelchair-Friendly Access', 'Assisted access to the camp seating area. Please tell us in advance.'],
            ['professional-photographer', 'Professional Photographer', 'A photographer available on request for family and couple portraits.'],
            ['halal-kitchen', 'Halal Kitchen', 'All food is prepared in our halal kitchen with fresh ingredients.'],
            ['drinking-water-and-refreshments', 'Drinking Water and Refreshments', 'Bottled water, chai and cold drinks throughout the trip.'],
            ['phone-charging-points', 'Phone Charging Points', 'Charging points at the main camp seating area.'],
        ];

        foreach ($rows as $i => [$slug, $name, $description]) {
            $facility = Facility::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($facility, 'facility-'.$slug, $name);
        }
    }

    private function highlights(): void
    {
        $rows = [
            ['dune-bashing', 'Dune Bashing', 'Ride the dunes with an experienced driver in a well-maintained 4x4.'],
            ['camel-riding', 'Camel Riding', 'A gentle camel ride across the sand, led by a local handler.'],
            ['folk-music-and-sufi-night', 'Folk Music and Sufi Night', 'Live folk songs and Sufi kalam around the camp fire.'],
            ['desert-bbq-and-dinner', 'Desert BBQ and Dinner', 'A traditional Pakistani dinner buffet with freshly grilled halal BBQ.'],
            ['stargazing', 'Stargazing', 'Dark skies far from city lights, with a guide to point out the constellations.'],
            ['falconry-display', 'Falconry Display', 'Meet a trained falcon and learn about this traditional desert art.'],
            ['henna-and-local-dress-photos', 'Henna and Local Dress Photos', 'Henna art and traditional costumes for unforgettable family photos.'],
            ['bonfire-and-chai', 'Bonfire and Chai', 'Warm up by the fire with fresh doodh patti chai and snacks.'],
        ];

        foreach ($rows as $i => [$slug, $title, $description]) {
            $highlight = Highlight::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($highlight, 'highlight-'.$slug, $title);
        }
    }

    private function addons(): void
    {
        $rows = [
            ['Henna art', 1000, 'Traditional henna design by a local artist.'],
            ['Photo with falcon', 1500, 'A short session with a trained falcon and a photo for your group.'],
            ['Professional photographer', 6000, 'A photographer for the whole trip with edited photos delivered in 3 days.'],
            ['Horse ride', 2500, 'A guided horse ride near the camp.'],
            ['Extra camel ride', 1500, 'An additional 30-minute camel ride.'],
            ['Quad bike (15 minutes)', 3500, 'A 15-minute quad ride with helmet and safety briefing.'],
            ['Bonfire and tea setup', 4000, 'A private bonfire for your group with chai and snacks.'],
            ['Local dress photo', 1200, 'Traditional dress for a family photo session.'],
        ];

        foreach ($rows as $i => [$name, $price, $description]) {
            Addon::updateOrCreate(['name' => $name], [
                'price' => $price,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function trustBadgesAndLinks(): void
    {
        $badges = [
            ['Local Expert Guides', 'Guides and drivers who grew up in the desert'],
            ['Family Friendly and Halal', 'Safe trips for all ages with halal food'],
            ['Flexible Payments', 'Bank transfer, JazzCash, Easypaisa, card or cash'],
            ['Free Cancellation', 'Cancel up to 24 hours before your trip'],
        ];

        foreach ($badges as $i => [$title, $subtitle]) {
            TrustBadge::updateOrCreate(['title' => $title], ['subtitle' => $subtitle, 'is_active' => true, 'sort_order' => $i + 1]);
        }

        $links = [
            [QuickLinkGroup::ActivityTypes, 'Cholistan safaris', '/category/cholistan-desert-safari'],
            [QuickLinkGroup::ActivityTypes, 'Thar village tours', '/category/thar-desert-safari'],
            [QuickLinkGroup::ActivityTypes, 'Thal family safaris', '/category/thal-desert-safari'],
            [QuickLinkGroup::ActivityTypes, 'Camel safaris', '/category/camel-safari'],
            [QuickLinkGroup::ActivityTypes, 'Overnight camps', '/category/overnight-desert-camping'],
            [QuickLinkGroup::ActivityTypes, 'Dune bashing', '/category/jeep-safari-dune-bashing'],
            [QuickLinkGroup::ActivityTypes, 'Quad biking', '/category/quad-biking-adventure'],
            [QuickLinkGroup::ActivityTypes, 'Heritage tours', '/category/sightseeing-heritage'],
            [QuickLinkGroup::PopularSearches, 'Sunset safari', '/search?q=sunset'],
            [QuickLinkGroup::PopularSearches, 'Camel ride', '/search?q=camel'],
            [QuickLinkGroup::PopularSearches, 'Derawar Fort', '/search?q=derawar'],
            [QuickLinkGroup::PopularSearches, 'Mithi and Islamkot', '/search?q=thar'],
            [QuickLinkGroup::PopularSearches, 'Stargazing', '/search?q=stargazing'],
            [QuickLinkGroup::PopularSearches, 'Family trips', '/search?q=family'],
            [QuickLinkGroup::PopularSearches, 'School and corporate trips', '/events'],
        ];

        foreach ($links as $i => [$group, $label, $url]) {
            QuickLink::updateOrCreate(
                ['section' => $group->value, 'label' => $label],
                ['url' => $url, 'is_active' => true, 'sort_order' => $i + 1],
            );
        }
    }

    private function eventPackages(): void
    {
        $rows = [
            ['school-trip-package', 'School Trip Package', '9:00 AM - 3:00 PM', 'A safe, educational day in the desert for school groups with teachers and a trained safety team.', 2500, 1800, ['Air-conditioned coaster transport', 'Guided desert walk and camel rides', 'Lunch and refreshments', 'First aid and safety team', 'Group photo session']],
            ['corporate-event-package', 'Corporate Event Package', '3:00 PM - 10:00 PM', 'Team building and company dinners under the stars, with a private camp for your team.', 9000, 6000, ['Private camp and seating', 'Team activities and dune drives', 'BBQ and traditional dinner buffet', 'Folk music and bonfire', 'Sound system and host']],
            ['wedding-and-family-gathering', 'Wedding and Family Gathering', '4:00 PM - 11:00 PM', 'Mehndi nights, engagement parties and family reunions in a decorated desert camp.', 11000, 7500, ['Decorated private camp', 'Traditional dinner for your guests', 'Folk music and dhol', 'Henna artists on request', 'Photographer on request']],
            ['product-launch-in-the-desert', 'Product Launch in the Desert', '4:00 PM - 9:00 PM', 'Launch your product against a golden desert backdrop with full event support.', 14000, 9000, ['Stage and sound setup', 'Media and photo area', 'Catering and refreshments', 'Guest transport', 'Event coordinator']],
            ['conference-and-retreat', 'Conference and Retreat', '9:00 AM - 6:00 PM', 'A full-day retreat with meeting tents, projectors, catering and desert activities during breaks.', 12000, 8000, ['Meeting tents with seating', 'Projector and sound system', 'Lunch and tea breaks', 'Desert activities during breaks', 'Prayer area and restrooms']],
        ];

        foreach ($rows as $i => [$slug, $title, $slot, $description, $adult, $child, $features]) {
            $package = EventPackage::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'time_slot' => $slot,
                'description' => $description,
                'adult_price' => $adult,
                'child_price' => $child,
                'features' => $features,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($package, 'event-'.$slug, $title);
        }
    }
}