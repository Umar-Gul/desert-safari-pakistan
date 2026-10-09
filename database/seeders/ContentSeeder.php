<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BlogStatus;
use App\Enums\FaqScope;
use App\Enums\GalleryItemType;
use App\Enums\ReviewStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\User;
use App\Support\CacheKeys;
use Database\Seeders\Support\SeedImages;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->settings();
        $this->faqs();
        $this->reviews();
        $this->blogs();
        $this->gallery();

        Setting::flushCache();
        CacheKeys::forgetCatalogue();
    }

    private function settings(): void
    {
        $values = [
            ['home', 'home_intro_title', 'Discover the deserts of Pakistan'],
            ['home', 'home_intro_html', '<p>From the golden dunes of Cholistan to the villages of Thar and the open plains of Thal, our local guides share the desert the way they know it: with warm hospitality, good food and respect for the land.</p><p>Choose a sunset safari, a night under the stars or a heritage day trip. Every booking includes clear pricing and a team that answers on WhatsApp.</p>'],
            ['social', 'twitter_handle', ''],
        ];

        foreach ($values as [$group, $key, $value]) {
            Setting::updateOrCreate(['key' => $key], ['group' => $group, 'type' => $key === 'home_intro_html' ? 'text' : 'string', 'value' => $value]);
        }
    }

    private function faqs(): void
    {
        $rows = [
            ['Is the food halal?', 'Yes. All food on our trips is prepared in a halal kitchen with fresh ingredients.'],
            ['What should I wear on a desert safari?', 'Light, loose cotton clothes, a scarf or cap, sunglasses and comfortable closed shoes. Bring a warm layer for winter evenings.'],
            ['What is the best season for a desert safari?', 'October to March is the most comfortable. In summer we run early-morning and late-evening trips to avoid the midday heat.'],
            ['Are the trips suitable for children?', 'Yes. Most trips welcome children with an adult. Adventure activities such as quad biking have minimum ages listed on the tour page.'],
            ['Is it safe for families and women travelling together?', 'Yes. Our camps are family-focused, our guides are local and respectful, and we provide separate clean restrooms and a prayer area.'],
            ['How do I pay?', 'After we confirm your booking we send payment details. You can pay by bank transfer, JazzCash, Easypaisa, card (through a payment link) or cash on arrival where offered.'],
            ['What is your cancellation policy?', 'Cancel free of charge up to 24 hours before the start time. Later cancellations may carry a fee. The full policy is on our cancellation and refund page.'],
            ['Which cities do you pick up from?', 'We offer pickup from Bahawalpur, Multan, Karachi, Lahore, Hyderabad and Islamabad. Choose your city at checkout.'],
            ['What should I bring?', 'Sun protection, water, a power bank, personal medicines and a jacket for the evening. We provide everything else.'],
            ['Is there a prayer area at the camps?', 'Yes. Every camp has a clean, covered prayer area with mats.'],
            ['Can we book a private trip for our group?', 'Yes. Choose the Private Camp option, or contact us for school trips, corporate events and family gatherings.'],
            ['What happens if the weather is bad?', 'We watch the forecast. If heat, wind or a sandstorm makes a trip unsafe, we will offer a new date or a refund of what you paid.'],
        ];

        foreach ($rows as $i => [$question, $answer]) {
            Faq::updateOrCreate(['question' => $question], [
                'answer' => $answer,
                'scope' => FaqScope::Global->value,
                'tour_id' => null,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function reviews(): void
    {
        $tours = Tour::query()->pluck('id', 'slug');

        $rows = [
            ['cholistan-sunset-jeep-safari', 'Ayesha Malik', 'Pakistan', 5, 'Perfect family evening', 'We took our parents and the kids. The drive was smooth, the sunset was beautiful and the dinner was delicious. The team looked after everyone.', 2],
            ['cholistan-overnight-desert-camp', 'Daniel Foster', 'United Kingdom', 5, 'The stars were unreal', 'We never expected such clear skies. The tents were comfortable, the music was lovely and breakfast at sunrise was a highlight.', 3],
            ['derawar-fort-heritage-day-trip', 'Hans Becker', 'Germany', 4, 'Great guide, long day', 'Our guide knew the history in detail and answered every question. A long day, but worth it for the fort alone.', 4],
            ['thar-village-and-desert-experience', 'Maria Lopez', 'Spain', 5, 'Warm, genuine hospitality', 'The families we met were so welcoming. The crafts, the food and the dunes at sunset made this one of the best days of our trip.', 5],
            ['thar-sunrise-camel-trek', 'Usman Tariq', 'Pakistan', 4, 'Calm and beautiful', 'A peaceful morning. The camel handler was patient with my kids and the breakfast was good.', 1],
            ['thal-desert-family-safari', 'Fatima Zahra', 'Pakistan', 5, 'Easy and fun for all ages', 'Our group had ages from 4 to 70 and everyone enjoyed it. Very well organised and great value.', 2],
            ['camel-ride-and-folk-music-evening', 'Sophie Laurent', 'France', 5, 'Music under the lights', 'The camel ride was gentle and the folk musicians were fantastic. We stayed late listening.', 3],
            ['dune-bashing-adventure', 'Ali Raza', 'Pakistan', 4, 'Thrilling', 'Our driver was careful but gave us a real adrenaline rush. Wear closed shoes and bring a scarf for the dust.', 1],
            ['luxury-vip-majlis-desert-night', 'Emily Carter', 'Canada', 5, 'Worth every rupee', 'Private camp, a lovely host and wonderful food. It felt special from the moment we arrived.', 6],
            ['stargazing-night-in-the-desert', 'Noor Fatima', 'Pakistan', 5, 'I saw the Milky Way', 'I had never seen so many stars. The guide was knowledgeable and the chai by the fire was perfect.', 2],
        ];

        foreach ($rows as [$slug, $name, $country, $rating, $title, $comment, $monthsAgo]) {
            if (! isset($tours[$slug])) {
                continue;
            }

            Review::updateOrCreate(
                ['tour_id' => $tours[$slug], 'name' => $name, 'comment' => $comment],
                [
                    'country' => $country,
                    'rating' => $rating,
                    'title' => $title,
                    'status' => ReviewStatus::Approved->value,
                    'visited_on' => now()->subMonths($monthsAgo)->toDateString(),
                ],
            );
        }
    }

    private function blogs(): void
    {
        $categories = [];

        foreach ([['travel-guides', 'Travel Guides'], ['desert-culture', 'Desert Culture'], ['tips-and-planning', 'Tips and Planning']] as $i => [$slug, $name]) {
            $categories[$slug] = BlogCategory::updateOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true, 'sort_order' => $i + 1])->id;
        }

        $authorId = User::query()->where('email', config('site.seed_admin.email'))->value('id');

        $posts = [
            ['best-time-to-visit-cholistan-desert', 'travel-guides', 'The Best Time to Visit the Cholistan Desert', 'When to go, what to expect and how to plan around the heat.', '<p>Cholistan is at its best from October to March, when daytime temperatures are pleasant and nights are cool enough for a bonfire. December and January are the coldest months, so pack a warm layer for the evening.</p><p>From April the heat builds quickly. If you travel in summer, choose an early-morning or late-evening trip and keep to the shade in the middle of the day.</p><p>After rare winter rain the desert can look surprisingly green, and seasonal pools attract birds. Ask our team about conditions before you book.</p>', 3],
            ['what-to-pack-for-a-desert-safari', 'tips-and-planning', 'What to Pack for a Desert Safari', 'A short checklist so you arrive comfortable and prepared.', '<p>Pack light cotton clothes in neutral colours, a scarf or cap, sunglasses and closed, comfortable shoes. Sand gets into everything, so avoid delicate fabrics.</p><p>Bring sun protection, a refillable water bottle, a power bank and any personal medicines. For overnight trips add a warm jacket, a torch and a spare set of clothes.</p><p>Leave valuables at the hotel. We provide water, refreshments, bedding for overnight camps and a prayer area at every camp.</p>', 10],
            ['derawar-fort-a-complete-visitor-guide', 'travel-guides', 'Derawar Fort: A Complete Visitor Guide', 'History, what to see and how to visit comfortably.', '<p>Derawar Fort rises from the Cholistan plain with forty bastions and thick brick walls. It was built centuries ago and later became a stronghold of the Abbasi rulers of Bahawalpur.</p><p>Plan to spend two to three hours walking the walls and courtyards, and add time for the royal mosque and the nearby tombs. A guide makes a big difference because many of the details are not signposted.</p><p>Start early, wear comfortable shoes and carry water. The light is best in the morning and again in the last hour before sunset.</p>', 17],
            ['life-in-the-thar-desert-villages-and-traditions', 'desert-culture', 'Life in the Thar Desert: Villages and Traditions', 'A look at the people, crafts and food of Tharparkar.', '<p>Thar is one of the most populated deserts in the world. Families live in round, thatched houses called chaunra, which stay cool in summer and warm in winter.</p><p>The region is known for embroidery, rilli quilts, pottery and bright clothing. Visitors are welcomed with tea and often invited to see how crafts are made.</p><p>When visiting, dress modestly, ask before taking photographs and buy crafts directly from the artisans where you can.</p>', 24],
            ['camel-riding-for-beginners-what-to-expect', 'tips-and-planning', 'Camel Riding for Beginners: What to Expect', 'How to mount, how it feels and how to stay comfortable.', '<p>A camel sits down so you can climb on, then stands up in two stages, back legs first. Lean back slightly as it rises and hold the handle in front of you.</p><p>The walk is slow and swaying. Most people feel comfortable within a few minutes. Wear long, loose trousers and closed shoes for comfort.</p><p>Your handler leads the camel throughout, so you can relax and enjoy the view. Tell the handler if you feel uneasy and the ride will be paused.</p>', 31],
            ['folk-music-and-food-of-the-desert', 'desert-culture', 'Folk Music and Food of the Desert', 'The sounds and flavours you will meet around a desert camp.', '<p>Evenings at camp often begin with the dhol and the algoza, a double flute played by skilled local musicians. Songs range from Sufi kalam to Saraiki and Marwari folk melodies.</p><p>Dinner is a generous spread of grilled halal meat, daal, fresh roti, rice and sweet dishes, served around the fire. Chai is never far away.</p><p>Join in if you feel like it: dancing is welcome, and musicians love an audience that listens.</p>', 38],
        ];

        foreach ($posts as [$slug, $category, $title, $excerpt, $content, $daysAgo]) {
            $post = Blog::updateOrCreate(['slug' => $slug], [
                'blog_category_id' => $categories[$category],
                'author_id' => $authorId,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'status' => BlogStatus::Published->value,
                'published_at' => now()->subDays($daysAgo),
            ]);

            SeedImages::attach($post, 'blog-'.$slug, $title);
        }
    }

    private function gallery(): void
    {
        $rows = [
            ['Golden dunes at sunset', 'Dunes'],
            ['Open jeep on the dunes', 'Dunes'],
            ['Camel caravan at dawn', 'Dunes'],
            ['Family camp at dusk', 'Camp'],
            ['Tents under the stars', 'Camp'],
            ['Folk musicians by the fire', 'Culture'],
            ['Traditional henna art', 'Culture'],
            ['Grilled BBQ and chai', 'Food'],
        ];

        foreach ($rows as $i => [$title, $category]) {
            $item = GalleryItem::updateOrCreate(['title' => $title], [
                'type' => GalleryItemType::Image->value,
                'category' => $category,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);

            SeedImages::attach($item, 'gallery-'.$title, $title);
        }
    }
}