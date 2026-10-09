<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FaqScope;
use App\Enums\TourBadge;
use App\Models\Addon;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\TourAdditionalInfo;
use App\Models\TourExclusion;
use App\Models\TourHighlight;
use App\Models\TourInclusion;
use App\Models\TourItineraryItem;
use App\Models\Vehicle;
use App\Support\CacheKeys;
use Database\Seeders\Support\SeedImages;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'slug');
        $deserts = Desert::query()->pluck('id', 'slug');
        $addons = Addon::query()->pluck('id', 'name');
        $camps = CampType::query()->pluck('id', 'slug');
        $vehicles = Vehicle::query()->pluck('id', 'slug');
        $templates = $this->templates();

        foreach ($this->tours() as $t) {
            $tpl = $templates[$t['type']];

            $tour = Tour::updateOrCreate(['slug' => $t['slug']], [
                'category_id' => $categories[$t['category']],
                'desert_id' => $deserts[$t['desert']] ?? null,
                'title' => $t['title'],
                'short_description' => $t['short'],
                'description' => '<p>'.$t['intro'].'</p><p>'.$tpl['about'].'</p>',
                'duration_hours' => $t['hours'],
                'base_price_adult' => $t['adult'],
                'base_price_child' => $t['child'],
                'discount_percent' => $t['disc'],
                'price_label' => 'per person',
                'has_dinner' => $t['dinner'],
                'has_breakfast' => $t['breakfast'],
                'has_guide' => $t['guide'],
                'has_transfer' => $t['transfer'],
                'has_itinerary' => true,
                'max_group_size' => $t['max'],
                'min_age' => $t['age'],
                'badges' => array_map(fn (string $b) => TourBadge::from($b), $t['badges']),
                'is_featured' => $t['featured'],
                'is_best_selling' => $t['best'],
                'is_active' => true,
                'sort_order' => $t['sort'],
            ]);

            $extra = collect($t['extra'])->map(fn ($slug) => $categories[$slug])->all();
            $tour->categories()->syncWithoutDetaching($extra);

            $tour->addons()->sync(collect($t['addons'])->map(fn ($name) => $addons[$name])->all());
            $tour->campTypes()->sync(collect($t['camps'])->map(fn ($slug) => $camps[$slug])->all());
            $tour->vehicles()->sync(collect($t['vehicles'])->map(fn ($slug) => $vehicles[$slug])->all());

            $this->children($tour, $t, $tpl);

            SeedImages::attach($tour, 'tour-'.$t['slug'], $t['title']);
        }

        CacheKeys::forgetCatalogue();
    }

    /**
     * @param  array<string, mixed>  $t
     * @param  array<string, mixed>  $tpl
     */
    private function children(Tour $tour, array $t, array $tpl): void
    {
        $id = $tour->id;

        TourHighlight::where('tour_id', $id)->delete();
        TourHighlight::insert(collect($tpl['highlights'])->map(fn ($text, $i) => [
            'tour_id' => $id, 'text' => $text, 'icon' => null, 'sort_order' => $i,
        ])->all());

        TourItineraryItem::where('tour_id', $id)->delete();
        TourItineraryItem::insert(collect($tpl['itinerary'])->map(fn ($row, $i) => [
            'tour_id' => $id, 'time_label' => $row[0], 'title' => $row[1], 'description' => $row[2], 'sort_order' => $i,
        ])->all());

        $included = [];
        if ($t['guide']) { $included[] = 'Experienced local guide'; }
        if ($t['transfer']) { $included[] = 'Pick and drop by private vehicle within the pickup area'; }
        if ($t['dinner']) { $included[] = 'Traditional Pakistani dinner (halal)'; }
        if ($t['breakfast']) { $included[] = 'Desert breakfast with paratha, eggs and chai'; }
        $included = array_merge($included, $tpl['included']);

        TourInclusion::where('tour_id', $id)->delete();
        TourInclusion::insert(collect($included)->map(fn ($text, $i) => ['tour_id' => $id, 'text' => $text, 'sort_order' => $i])->all());

        TourExclusion::where('tour_id', $id)->delete();
        TourExclusion::insert(collect([
            'Personal expenses and tips',
            'Travel insurance',
            'Optional add-ons such as photography, henna or quad rides',
        ])->map(fn ($text, $i) => ['tour_id' => $id, 'text' => $text, 'sort_order' => $i])->all());

        TourAdditionalInfo::where('tour_id', $id)->delete();
        TourAdditionalInfo::insert(collect($tpl['info'])->map(fn ($row, $i) => [
            'tour_id' => $id, 'title' => $row[0], 'content' => $row[1], 'sort_order' => $i,
        ])->all());

        Faq::where('tour_id', $id)->delete();
        foreach ($tpl['faqs'] as $i => [$question, $answer]) {
            Faq::create([
                'scope' => FaqScope::Tour->value,
                'tour_id' => $id,
                'question' => $question,
                'answer' => $answer,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }

    /** @return array<string, array<string, mixed>> */
    private function templates(): array
    {
        return [
            'sunset' => [
                'about' => 'Watch the dunes turn from gold to copper as the sun sets, then relax at camp with chai, folk music and a traditional dinner.',
                'highlights' => ['Scenic desert drive with photo stops', 'Golden-hour views from the top of the dunes', 'Fresh chai and snacks at sunset', 'Folk music and a halal dinner at camp'],
                'included' => ['Bottled water and refreshments', 'Prayer area at camp'],
                'itinerary' => [
                    ['3:30 PM', 'Pickup', 'Our driver collects you from your hotel or the agreed meeting point.'],
                    ['4:30 PM', 'Desert drive', 'Cross the sand tracks with photo stops along the way.'],
                    ['6:00 PM', 'Sunset at the dunes', 'Chai, snacks and time for pictures as the sun goes down.'],
                    ['7:00 PM', 'Camp evening', 'Folk music, a bonfire and a traditional dinner.'],
                    ['9:30 PM', 'Return', 'Drop-off at your hotel or pickup point.'],
                ],
                'faqs' => [
                    ['What time should I be ready?', 'Please be ready 15 minutes before the pickup time confirmed on your voucher.'],
                    ['Is the trip suitable for children?', 'Yes. Children are welcome with an adult, seat belts are provided and drivers adjust their pace for families.'],
                ],
                'info' => [
                    ['What to wear', 'Light cotton clothes, a scarf or cap and comfortable shoes. Evenings can be cool in winter.'],
                    ['Best season', 'October to March is the most comfortable. In summer we run later departures.'],
                ],
            ],
            'overnight' => [
                'about' => 'Spend a night in the desert: arrive in the afternoon, enjoy sunset, dinner and music, sleep in a comfortable camp and wake up to sunrise and breakfast.',
                'highlights' => ['Tent with proper bedding and lighting', 'Stargazing far from city lights', 'Bonfire, folk music and Sufi night', 'Desert breakfast at sunrise'],
                'included' => ['Tent, mattress, pillows and blankets', 'Bottled water and refreshments', 'Prayer area and clean restrooms at camp'],
                'itinerary' => [
                    ['2:00 PM', 'Pickup and transfer', 'Head out to the camp in comfortable 4x4 vehicles.'],
                    ['4:00 PM', 'Camp check-in', 'Welcome tea and time to settle into your tent.'],
                    ['5:30 PM', 'Dune activities', 'Camel ride and sunset on the dunes.'],
                    ['8:00 PM', 'Dinner and music', 'Traditional dinner by the bonfire with live folk music.'],
                    ['6:30 AM', 'Sunrise and breakfast', 'Fresh paratha, eggs and chai after sunrise.'],
                    ['9:00 AM', 'Return', 'Transfer back to the pickup point.'],
                ],
                'faqs' => [
                    ['What are the sleeping arrangements?', 'Guests sleep in clean tents with mattresses, pillows and blankets. Family and private tents are available.'],
                    ['Are there washrooms at the camp?', 'Yes. Clean restrooms with running water are available near the tents.'],
                ],
                'info' => [
                    ['What to bring', 'A warm layer for the night, personal medicines, a torch and a power bank.'],
                    ['Prayer', 'A clean prayer area is available at the camp.'],
                ],
            ],
            'heritage' => [
                'about' => 'A guided day through the forts, mosques and royal tombs of the region with a local historian who knows the stories behind the walls.',
                'highlights' => ['Guided visit to the historic fort', 'Royal mosque and tombs stop', 'Lunch with local dishes', 'Desert drive back with a sunset stop'],
                'included' => ['Entry tickets where required', 'Bottled water and refreshments', 'Lunch with local dishes'],
                'itinerary' => [
                    ['7:00 AM', 'Early pickup', 'Start early to beat the heat.'],
                    ['10:00 AM', 'Fort visit', 'A guided walk through the walls, gates and courtyards.'],
                    ['1:00 PM', 'Lunch', 'Local food at a family-run restaurant.'],
                    ['3:00 PM', 'Mosque and tombs', 'Visit the royal mosque and nearby tombs.'],
                    ['5:00 PM', 'Return', 'Desert drive back with a sunset stop.'],
                ],
                'faqs' => [
                    ['Can I take photos at the site?', 'Photography is generally allowed outdoors. Your guide will tell you where it is not permitted.'],
                    ['How much walking is involved?', 'About 2 km of easy walking over the day. Comfortable shoes are recommended.'],
                ],
                'info' => [
                    ['Dress code', 'Modest, loose clothing. Carry a hat and sunglasses.'],
                    ['Best season', 'October to March. Summer visits start very early to avoid the midday heat.'],
                ],
            ],
            'village' => [
                'about' => 'Visit Thar villages, meet local families, see traditional crafts and walk the dunes near Islamkot as the light softens.',
                'highlights' => ['Village visit with traditional tea', 'Handicraft and embroidery stop', 'Dunes near Islamkot at sunset', 'Local lunch with Thari dishes'],
                'included' => ['Village visits and local host', 'Lunch with local dishes', 'Bottled water and refreshments'],
                'itinerary' => [
                    ['8:00 AM', 'Pickup', 'Meet your guide in Mithi.'],
                    ['9:30 AM', 'Village visit', 'Meet a local family and learn about daily life in Thar.'],
                    ['12:30 PM', 'Lunch', 'A home-style meal prepared by village hosts.'],
                    ['2:30 PM', 'Crafts', 'See embroidery, rilli quilts and pottery being made.'],
                    ['4:30 PM', 'Dunes at sunset', 'Walk the dunes near Islamkot.'],
                    ['6:00 PM', 'Return', 'Drive back to Mithi.'],
                ],
                'faqs' => [
                    ['Is it respectful to take photos of villagers?', 'Always ask first. Your guide will help you ask politely and many families are happy to pose.'],
                    ['Are the roads comfortable?', 'Main roads are good. Some village tracks are sandy, so we use 4x4 vehicles.'],
                ],
                'info' => [
                    ['Cultural notes', 'Dress modestly and follow your guide on greetings and photography.'],
                    ['Shopping', 'Handicrafts are sold directly by artisans. Carry some cash.'],
                ],
            ],
            'camel' => [
                'about' => 'Ride a camel across the dunes with a local handler who knows the animals well, and finish with fresh chai on the sand.',
                'highlights' => ['A camel ride of about 45 minutes', 'Friendly local handlers', 'Photo stops on the dunes', 'Chai and snacks at the end'],
                'included' => ['Camel ride with handler', 'Chai and snacks', 'Bottled water'],
                'itinerary' => [
                    ['Meeting', 'Meet your guide', 'Safety briefing and an introduction to your camel.'],
                    ['Ride', 'Camel ride', 'A slow, comfortable ride over the dunes with photo stops.'],
                    ['Break', 'Chai on the dunes', 'Fresh chai and snacks while you enjoy the view.'],
                    ['Finish', 'Return', 'Back to the meeting point.'],
                ],
                'faqs' => [
                    ['Is camel riding safe for beginners?', 'Yes. Handlers lead each camel and help you mount and dismount safely.'],
                    ['Can small children ride?', 'Children from 5 years can ride with an adult. Younger children can sit with a parent at the handler\'s discretion.'],
                ],
                'info' => [
                    ['What to wear', 'Long, comfortable trousers and closed shoes make the ride easier.'],
                    ['Health notes', 'Guests with back or neck problems should speak to us before booking.'],
                ],
            ],
            'adventure' => [
                'about' => 'Feel the thrill of the dunes with an experienced driver or instructor, safety gear and a short briefing before every ride.',
                'highlights' => ['Dune bashing with an experienced driver', 'Safety briefing and equipment', 'Sand slopes for photos and sliding', 'Refreshments at the end'],
                'included' => ['Safety equipment and briefing', 'Bottled water and refreshments', 'Instructor or driver'],
                'itinerary' => [
                    ['Meeting', 'Safety briefing', 'Learn the rules and get fitted with safety gear.'],
                    ['Ride', 'Dune adventure', 'Ride the dunes in small groups with a guide.'],
                    ['Break', 'Photo stop', 'Time for pictures and a rest.'],
                    ['Finish', 'Refreshments and return', 'Cold drinks and chai before heading back.'],
                ],
                'faqs' => [
                    ['Do I need any experience?', 'No. Our team gives a full briefing and adjusts the speed to your comfort.'],
                    ['Are there age and health limits?', 'Guests with heart, back, neck or pregnancy-related concerns should not take part. Minimum ages are listed on each trip.'],
                ],
                'info' => [
                    ['What to wear', 'Closed shoes, long trousers and sunglasses. A scarf helps against dust.'],
                    ['Safety', 'Always follow your instructor. Rides can be paused at any time if you feel uncomfortable.'],
                ],
            ],
            'expedition' => [
                'about' => 'A two-day journey into remote dunes with an experienced convoy, a night in a self-contained camp and long, quiet horizons.',
                'highlights' => ['Two-day convoy with recovery support', 'Night camp deep in the dunes', 'Sunset and sunrise on high red dunes', 'Hot meals prepared by our camp crew'],
                'included' => ['Convoy with recovery vehicle', 'Camp equipment and meals', 'Drinking water and refreshments'],
                'itinerary' => [
                    ['Day 1, 7:00 AM', 'Departure', 'Meet the convoy and head for the desert.'],
                    ['Day 1, 1:00 PM', 'Lunch break', 'A hot meal at a shaded rest point.'],
                    ['Day 1, 5:30 PM', 'Camp and sunset', 'Set up camp and watch the sunset from the dunes.'],
                    ['Day 2, 6:30 AM', 'Sunrise and breakfast', 'Breakfast with a view across the dunes.'],
                    ['Day 2, 11:00 AM', 'Return', 'Drive back with a stop for photographs.'],
                ],
                'faqs' => [
                    ['How remote is this trip?', 'Very. A recovery vehicle, satellite-ready communication and first aid are part of every convoy.'],
                    ['What fitness level is needed?', 'No special fitness is needed, but the days are long and conditions are basic.'],
                ],
                'info' => [
                    ['What to bring', 'A warm layer for the night, sun protection, personal medicines and a power bank.'],
                    ['Weather', 'We monitor the weather and may change the route or date for safety.'],
                ],
            ],
            'stargazing' => [
                'about' => 'Leave the city lights behind and spend an evening under a sky full of stars, with a guide who points out the constellations.',
                'highlights' => ['Dark skies far from city lights', 'Guide to the constellations and planets', 'Warm chai and a light dinner', 'Blankets and seating around a fire'],
                'included' => ['Blankets and seating', 'Bottled water and refreshments', 'Prayer area at camp'],
                'itinerary' => [
                    ['6:00 PM', 'Pickup', 'Drive out of town as the light fades.'],
                    ['7:30 PM', 'Camp and dinner', 'A light halal dinner and hot chai.'],
                    ['8:30 PM', 'Stargazing', 'Constellations, planets and the Milky Way on clear nights.'],
                    ['11:00 PM', 'Return', 'Drive back to the city.'],
                ],
                'faqs' => [
                    ['What if the sky is cloudy?', 'We check the forecast and will offer a new date at no cost if clouds make stargazing impossible.'],
                    ['Do I need a telescope?', 'No. We bring binoculars and a star chart. You may bring your own telescope too.'],
                ],
                'info' => [
                    ['Best nights', 'Moonless nights between October and March give the clearest views.'],
                    ['What to wear', 'Warm layers. The desert gets cold after sunset in winter.'],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function tours(): array
    {
        return [
            ['slug' => 'cholistan-sunset-jeep-safari', 'title' => 'Cholistan Sunset Jeep Safari', 'type' => 'sunset', 'category' => 'jeep-safari-dune-bashing', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 5, 'adult' => 6500, 'child' => 4000, 'disc' => 10, 'dinner' => true, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 40, 'age' => 3, 'badges' => ['top_rated', 'best_selling'], 'featured' => true, 'best' => true, 'short' => 'Open-jeep drive over the Cholistan dunes, sunset with chai and a traditional dinner at camp.', 'intro' => 'Our most popular evening trip takes you from Bahawalpur into the golden dunes of Cholistan just in time for the sunset.', 'addons' => ['Henna art', 'Photo with falcon', 'Bonfire and tea setup', 'Local dress photo'], 'camps' => ['standard-family-camp', 'premium-camp'], 'vehicles' => ['open-safari-jeep', 'toyota-land-cruiser'], 'sort' => 1],
            ['slug' => 'cholistan-overnight-desert-camp', 'title' => 'Cholistan Overnight Desert Camp', 'type' => 'overnight', 'category' => 'overnight-desert-camping', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 20, 'adult' => 14500, 'child' => 9000, 'disc' => 15, 'dinner' => true, 'breakfast' => true, 'guide' => true, 'transfer' => true, 'max' => 30, 'age' => 5, 'badges' => ['best_selling'], 'featured' => true, 'best' => true, 'short' => 'A night in the Cholistan dunes with dinner, folk music, stargazing and breakfast at sunrise.', 'intro' => 'Experience the desert after dark: a comfortable camp, a warm bonfire and a sky full of stars.', 'addons' => ['Henna art', 'Bonfire and tea setup', 'Professional photographer', 'Extra camel ride'], 'camps' => ['standard-family-camp', 'premium-camp', 'vip-majlis-camp', 'private-camp'], 'vehicles' => ['toyota-land-cruiser', 'toyota-prado', 'toyota-hilux-double-cabin'], 'sort' => 2],
            ['slug' => 'derawar-fort-heritage-day-trip', 'title' => 'Derawar Fort Heritage Day Trip', 'type' => 'heritage', 'category' => 'sightseeing-heritage', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 10, 'adult' => 9500, 'child' => 6000, 'disc' => 0, 'dinner' => false, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 20, 'age' => 6, 'badges' => ['special'], 'featured' => false, 'best' => false, 'short' => 'A guided day at Derawar Fort, the royal mosque and tombs, with lunch and a desert drive back.', 'intro' => 'Walk through the walls of one of Pakistan\'s most striking forts, rising from the Cholistan sands.', 'addons' => ['Professional photographer', 'Local dress photo'], 'camps' => [], 'vehicles' => ['toyota-land-cruiser', 'toyota-prado'], 'sort' => 3],
            ['slug' => 'thar-village-and-desert-experience', 'title' => 'Thar Village and Desert Experience', 'type' => 'village', 'category' => 'thar-desert-safari', 'extra' => ['sightseeing-heritage'], 'desert' => 'thar-desert', 'hours' => 12, 'adult' => 12000, 'child' => 7500, 'disc' => 10, 'dinner' => false, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 16, 'age' => 6, 'badges' => ['top_rated'], 'featured' => true, 'best' => false, 'short' => 'Meet Thar families in Mithi and Islamkot, see traditional crafts and walk the dunes at sunset.', 'intro' => 'Thar is a living desert. This day trip introduces you to the people, crafts and food that make it special.', 'addons' => ['Henna art', 'Local dress photo', 'Professional photographer'], 'camps' => [], 'vehicles' => ['toyota-hilux-double-cabin', 'toyota-prado'], 'sort' => 4],
            ['slug' => 'thar-sunrise-camel-trek', 'title' => 'Thar Sunrise Camel Trek', 'type' => 'camel', 'category' => 'camel-safari', 'extra' => ['thar-desert-safari'], 'desert' => 'thar-desert', 'hours' => 3, 'adult' => 4500, 'child' => 3000, 'disc' => 0, 'dinner' => false, 'breakfast' => true, 'guide' => true, 'transfer' => false, 'max' => 20, 'age' => 5, 'badges' => [], 'featured' => false, 'best' => false, 'short' => 'A gentle camel trek over the Thar dunes at sunrise, followed by a desert breakfast.', 'intro' => 'Start the day slowly with the cool light of dawn, a camel under you and nothing but sand ahead.', 'addons' => ['Extra camel ride', 'Photo with falcon', 'Henna art'], 'camps' => [], 'vehicles' => [], 'sort' => 5],
            ['slug' => 'thal-desert-family-safari', 'title' => 'Thal Desert Family Safari', 'type' => 'sunset', 'category' => 'thal-desert-safari', 'extra' => ['jeep-safari-dune-bashing'], 'desert' => 'thal-desert', 'hours' => 5, 'adult' => 5500, 'child' => 3500, 'disc' => 20, 'dinner' => true, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 50, 'age' => 0, 'badges' => ['special'], 'featured' => true, 'best' => false, 'short' => 'A relaxed family safari on the Thal plains with a sunset stop and a traditional dinner.', 'intro' => 'Gentle dunes, comfortable vehicles and a pace that works for grandparents and toddlers alike.', 'addons' => ['Henna art', 'Local dress photo', 'Bonfire and tea setup', 'Horse ride'], 'camps' => ['standard-family-camp', 'premium-camp', 'private-camp'], 'vehicles' => ['toyota-coaster-bus', 'toyota-land-cruiser'], 'sort' => 6],
            ['slug' => 'camel-ride-and-folk-music-evening', 'title' => 'Camel Ride and Folk Music Evening', 'type' => 'camel', 'category' => 'camel-safari', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 5, 'adult' => 7000, 'child' => 4500, 'disc' => 10, 'dinner' => true, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 30, 'age' => 5, 'badges' => ['top_rated'], 'featured' => true, 'best' => true, 'short' => 'An evening camel ride followed by live folk music and a traditional dinner at camp.', 'intro' => 'Ride out at golden hour and return to the sound of dhol, algoza and a camp dinner under the lights.', 'addons' => ['Henna art', 'Extra camel ride', 'Photo with falcon', 'Local dress photo'], 'camps' => ['standard-family-camp', 'premium-camp'], 'vehicles' => ['toyota-land-cruiser', 'toyota-hilux-double-cabin'], 'sort' => 7],
            ['slug' => 'dune-bashing-adventure', 'title' => 'Dune Bashing Adventure', 'type' => 'adventure', 'category' => 'jeep-safari-dune-bashing', 'extra' => ['thal-desert-safari'], 'desert' => 'thal-desert', 'hours' => 3, 'adult' => 5000, 'child' => 3500, 'disc' => 0, 'dinner' => false, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 24, 'age' => 8, 'badges' => ['best_selling'], 'featured' => false, 'best' => true, 'short' => 'Three hours of dune bashing in a 4x4 with an experienced driver and a safety briefing.', 'intro' => 'Hold on tight: this is the fastest way to feel what the dunes are really like.', 'addons' => ['Quad bike (15 minutes)', 'Professional photographer'], 'camps' => [], 'vehicles' => ['toyota-hilux-double-cabin', 'open-safari-jeep'], 'sort' => 8],
            ['slug' => 'quad-biking-dunes-experience', 'title' => 'Quad Biking Dunes Experience', 'type' => 'adventure', 'category' => 'quad-biking-adventure', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 2, 'adult' => 8500, 'child' => 5000, 'disc' => 15, 'dinner' => false, 'breakfast' => false, 'guide' => true, 'transfer' => false, 'max' => 12, 'age' => 12, 'badges' => [], 'featured' => false, 'best' => false, 'short' => 'A guided quad ride across the Cholistan dunes with helmet, gloves and an instructor.', 'intro' => 'Short, thrilling and well supervised: ideal if you want adventure without a full-day commitment.', 'addons' => ['Professional photographer', 'Bonfire and tea setup'], 'camps' => [], 'vehicles' => [], 'sort' => 9],
            ['slug' => 'kharan-desert-expedition', 'title' => 'Kharan Desert Expedition (2 Days)', 'type' => 'expedition', 'category' => 'overnight-desert-camping', 'extra' => ['sightseeing-heritage'], 'desert' => 'kharan-desert', 'hours' => 28, 'adult' => 28000, 'child' => 18000, 'disc' => 10, 'dinner' => true, 'breakfast' => true, 'guide' => true, 'transfer' => true, 'max' => 12, 'age' => 10, 'badges' => ['special'], 'featured' => false, 'best' => false, 'short' => 'A two-day convoy to the high red dunes of Kharan with a night camp and full recovery support.', 'intro' => 'For travellers who want a real expedition: remote dunes, big skies and a crew that has made this journey many times.', 'addons' => ['Professional photographer', 'Bonfire and tea setup'], 'camps' => ['premium-camp', 'private-camp'], 'vehicles' => ['toyota-land-cruiser', 'toyota-hilux-double-cabin'], 'sort' => 10],
            ['slug' => 'luxury-vip-majlis-desert-night', 'title' => 'Luxury VIP Majlis Desert Night', 'type' => 'overnight', 'category' => 'overnight-desert-camping', 'extra' => ['cholistan-desert-safari'], 'desert' => 'cholistan-desert', 'hours' => 14, 'adult' => 22000, 'child' => 14000, 'disc' => 0, 'dinner' => true, 'breakfast' => true, 'guide' => true, 'transfer' => true, 'max' => 16, 'age' => 8, 'badges' => ['top_rated', 'special'], 'featured' => true, 'best' => false, 'short' => 'A private majlis-style camp with a dedicated host, fine dining, live music and breakfast.', 'intro' => 'Our most comfortable overnight experience, designed for families and small groups who want privacy and service.', 'addons' => ['Henna art', 'Professional photographer', 'Horse ride', 'Local dress photo'], 'camps' => ['vip-majlis-camp', 'private-camp'], 'vehicles' => ['toyota-land-cruiser', 'toyota-prado'], 'sort' => 11],
            ['slug' => 'stargazing-night-in-the-desert', 'title' => 'Stargazing Night in the Desert', 'type' => 'stargazing', 'category' => 'overnight-desert-camping', 'extra' => ['thar-desert-safari'], 'desert' => 'thar-desert', 'hours' => 8, 'adult' => 9000, 'child' => 5500, 'disc' => 12, 'dinner' => true, 'breakfast' => false, 'guide' => true, 'transfer' => true, 'max' => 20, 'age' => 6, 'badges' => ['top_rated'], 'featured' => false, 'best' => false, 'short' => 'An evening under the stars with a guide, a light dinner, hot chai and blankets by the fire.', 'intro' => 'Thar has some of the darkest skies in the country. Bring your curiosity and a warm jacket.', 'addons' => ['Bonfire and tea setup', 'Professional photographer'], 'camps' => ['standard-family-camp', 'premium-camp'], 'vehicles' => ['toyota-prado', 'toyota-hilux-double-cabin'], 'sort' => 12],
        ];
    }
}