<?php

declare(strict_types=1);

use App\Enums\AdjustmentType;
use App\Enums\BlogStatus;
use App\Enums\FaqScope;
use App\Enums\GalleryItemType;
use App\Enums\QuickLinkGroup;
use App\Models\Addon;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Desert;
use App\Models\EventPackage;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Highlight;
use App\Models\QuickLink;
use App\Models\Tour;
use App\Models\TrustBadge;
use App\Models\Vehicle;

/*
| One entry per CRUD module. The key is the URL segment and route name
| (admin.{key}.index ...). Field types:
|   text, textarea, richtext, number, select, checkbox, date, datetime,
|   slug, lines (one item per line, saved as an array), image (cover collection).
| Field options: label, required, max, hint, default, section, rules (replaces
| the generated rules), unique, uppercase, integer (number), blank (select),
| options (enum class) or options_from (model + label).
| Column types: text (default), image, bool, enum (optional "colors" map),
| date, money.
| No closures here, so `php artisan config:cache` keeps working.
*/

$seo = [
    'meta_title' => ['label' => 'Meta title', 'type' => 'text', 'max' => 70, 'section' => 'SEO'],
    'meta_description' => ['label' => 'Meta description', 'type' => 'textarea', 'max' => 320, 'rows' => 3, 'section' => 'SEO'],
    'canonical' => ['label' => 'Canonical URL', 'type' => 'text', 'max' => 255, 'section' => 'SEO', 'hint' => 'Leave empty to use the page address.'],
];

$display = [
    'is_active' => ['label' => 'Active (visible on the site)', 'type' => 'checkbox', 'default' => true, 'section' => 'Display'],
    'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'integer' => true, 'default' => 0, 'section' => 'Display', 'hint' => 'Lower numbers appear first.'],
];

$slug = ['label' => 'URL slug', 'type' => 'slug', 'hint' => 'Leave empty to generate it from the name.'];
$description = ['label' => 'Description', 'type' => 'textarea', 'max' => 3000, 'rows' => 5];
$image = ['label' => 'Image', 'type' => 'image', 'hint' => 'JPG, PNG or WebP, up to 4 MB.'];

$activeColumns = [
    ['label' => 'Active', 'key' => 'is_active', 'type' => 'bool'],
    ['label' => 'Order', 'key' => 'sort_order'],
];

return [

    'categories' => [
        'model' => Category::class,
        'singular' => 'Category',
        'plural' => 'Categories',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name'],
        'counts' => ['tours'],
        'block_delete' => ['tours' => 'tours'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Tours', 'key' => 'tours_count'],
            ['label' => 'In menu', 'key' => 'show_in_menu', 'type' => 'bool'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
            'description' => $description,
            'cover' => $image,
            'show_in_menu' => ['label' => 'Show in the header menu list', 'type' => 'checkbox', 'section' => 'Menus'],
            'is_quick_link' => ['label' => 'Show as a quick link in the top bar', 'type' => 'checkbox', 'section' => 'Menus'],
        ], $seo, $display),
    ],

    'deserts' => [
        'model' => Desert::class,
        'singular' => 'Desert',
        'plural' => 'Deserts',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name', 'location'],
        'counts' => ['tours'],
        'block_delete' => ['tours' => 'tours'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Location', 'key' => 'location'],
            ['label' => 'Tours', 'key' => 'tours_count'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
            'location' => ['label' => 'Location', 'type' => 'text', 'hint' => 'Example: Bahawalpur, Punjab'],
            'description' => $description,
            'cover' => $image,
        ], $seo, $display),
    ],

    'addons' => [
        'model' => Addon::class,
        'singular' => 'Add-on',
        'plural' => 'Add-ons',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name'],
        'counts' => ['tours'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Price', 'key' => 'price', 'type' => 'money'],
            ['label' => 'Tours', 'key' => 'tours_count'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'description' => $description,
            'price' => ['label' => 'Default price', 'type' => 'number', 'required' => true, 'default' => 0, 'hint' => 'Each tour can override this price in the tour editor.'],
            'cover' => $image,
        ], $display),
    ],

    'camp-types' => [
        'model' => CampType::class,
        'singular' => 'Camp type',
        'plural' => 'Camp types',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name'],
        'counts' => ['tours'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Modifier', 'key' => 'price_modifier_type', 'type' => 'enum'],
            ['label' => 'Value', 'key' => 'price_modifier_value'],
            ['label' => 'Tours', 'key' => 'tours_count'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
            'description' => $description,
            'price_modifier_type' => ['label' => 'Price modifier type', 'type' => 'select', 'options' => AdjustmentType::class, 'required' => true, 'default' => 'fixed'],
            'price_modifier_value' => ['label' => 'Price modifier value', 'type' => 'number', 'required' => true, 'default' => 0, 'hint' => 'Fixed: amount added per guest. Percent: share of the people total.'],
            'cover' => $image,
        ], $display),
    ],

    'vehicles' => [
        'model' => Vehicle::class,
        'singular' => 'Vehicle',
        'plural' => 'Vehicles',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name'],
        'counts' => ['tours'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Seats', 'key' => 'capacity'],
            ['label' => 'Extra price', 'key' => 'price_modifier', 'type' => 'money'],
            ['label' => 'Tours', 'key' => 'tours_count'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
            'description' => $description,
            'capacity' => ['label' => 'Seats', 'type' => 'number', 'integer' => true, 'required' => true, 'default' => 0],
            'price_modifier' => ['label' => 'Extra price', 'type' => 'number', 'required' => true, 'default' => 0, 'hint' => 'Charged once per booking when this vehicle is chosen.'],
            'cover' => $image,
        ], $display),
    ],

    'facilities' => [
        'model' => Facility::class,
        'singular' => 'Facility',
        'plural' => 'Facilities',
        'label_key' => 'name',
        'cover' => true,
        'toggle' => true,
        'search' => ['name'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Name', 'key' => 'name'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
            'description' => $description,
            'cover' => $image,
        ], $display),
    ],

    'highlights' => [
        'model' => Highlight::class,
        'singular' => 'Highlight',
        'plural' => 'Highlights',
        'label_key' => 'title',
        'cover' => true,
        'toggle' => true,
        'search' => ['title'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Title', 'key' => 'title'],
        ], $activeColumns),
        'fields' => array_merge([
            'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 150],
            'slug' => $slug,
            'description' => $description,
            'cover' => $image,
        ], $display),
    ],

    'trust-badges' => [
        'model' => TrustBadge::class,
        'singular' => 'Trust badge',
        'plural' => 'Trust badges',
        'label_key' => 'title',
        'cover' => true,
        'toggle' => true,
        'search' => ['title', 'subtitle'],
        'columns' => array_merge([
            ['label' => 'Icon', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Title', 'key' => 'title'],
            ['label' => 'Subtitle', 'key' => 'subtitle'],
        ], $activeColumns),
        'fields' => array_merge([
            'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 120, 'hint' => 'Example: Top Rated Company'],
            'subtitle' => ['label' => 'Subtitle', 'type' => 'text', 'max' => 160, 'hint' => 'Example: Best Service Guaranteed'],
            'cover' => ['label' => 'Icon', 'type' => 'image', 'hint' => 'Small square icon. JPG, PNG or WebP.'],
        ], $display),
    ],

    'quick-links' => [
        'model' => QuickLink::class,
        'singular' => 'Quick link',
        'plural' => 'Quick links',
        'label_key' => 'label',
        'toggle' => true,
        'search' => ['label', 'url'],
        'order' => [['section', 'asc'], ['sort_order', 'asc'], ['id', 'asc']],
        'columns' => array_merge([
            ['label' => 'Label', 'key' => 'label'],
            ['label' => 'Section', 'key' => 'section', 'type' => 'enum'],
            ['label' => 'Link', 'key' => 'url'],
        ], $activeColumns),
        'fields' => array_merge([
            'section' => ['label' => 'Section', 'type' => 'select', 'options' => QuickLinkGroup::class, 'required' => true, 'default' => 'activity_types'],
            'label' => ['label' => 'Label', 'type' => 'text', 'required' => true, 'max' => 120],
            'url' => ['label' => 'Link', 'type' => 'text', 'required' => true, 'max' => 255, 'hint' => 'Example: /tours or https://...'],
            'icon' => ['label' => 'Icon name', 'type' => 'text', 'max' => 50, 'hint' => 'Optional. Example: tickets, tour, food.'],
        ], $display),
    ],

    'event-packages' => [
        'model' => EventPackage::class,
        'singular' => 'Event package',
        'plural' => 'Event packages',
        'label_key' => 'title',
        'cover' => true,
        'toggle' => true,
        'search' => ['title'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Title', 'key' => 'title'],
            ['label' => 'Time', 'key' => 'time_slot'],
            ['label' => 'Adult', 'key' => 'adult_price', 'type' => 'money'],
            ['label' => 'Child', 'key' => 'child_price', 'type' => 'money'],
        ], $activeColumns),
        'fields' => array_merge([
            'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 150],
            'slug' => $slug,
            'time_slot' => ['label' => 'Time', 'type' => 'text', 'max' => 100, 'hint' => 'Example: 09:00 AM to 11:30 AM'],
            'description' => $description,
            'adult_price' => ['label' => 'Adult price', 'type' => 'number', 'required' => true, 'default' => 0],
            'child_price' => ['label' => 'Child price', 'type' => 'number', 'required' => true, 'default' => 0],
            'features' => ['label' => 'Features', 'type' => 'lines', 'hint' => 'One feature per line, for example: Camping, Competitions and Prizes, Rides Included.'],
            'cover' => $image,
        ], $display),
    ],

    'gallery-items' => [
        'model' => GalleryItem::class,
        'singular' => 'Gallery item',
        'plural' => 'Gallery',
        'label_key' => 'title',
        'cover' => true,
        'toggle' => true,
        'search' => ['title', 'category'],
        'columns' => array_merge([
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Title', 'key' => 'title'],
            ['label' => 'Type', 'key' => 'type', 'type' => 'enum'],
            ['label' => 'Category', 'key' => 'category'],
        ], $activeColumns),
        'fields' => array_merge([
            'title' => ['label' => 'Title', 'type' => 'text', 'max' => 150],
            'type' => ['label' => 'Type', 'type' => 'select', 'options' => GalleryItemType::class, 'required' => true, 'default' => 'image'],
            'category' => ['label' => 'Group', 'type' => 'text', 'max' => 100, 'hint' => 'Used to group photos. Example: Dunes, Camp, Culture, Food.'],
            'cover' => ['label' => 'Photo', 'type' => 'image', 'hint' => 'JPG, PNG or WebP, up to 4 MB.'],
            'url' => ['label' => 'Video link', 'type' => 'text', 'max' => 255, 'hint' => 'Only for videos (YouTube or a file link).'],
        ], $display),
    ],

    'faqs' => [
        'model' => Faq::class,
        'singular' => 'FAQ',
        'plural' => 'FAQs',
        'label_key' => 'question',
        'toggle' => true,
        'search' => ['question', 'answer'],
        'with' => ['tour'],
        'columns' => array_merge([
            ['label' => 'Question', 'key' => 'question'],
            ['label' => 'Scope', 'key' => 'scope', 'type' => 'enum'],
            ['label' => 'Tour', 'key' => 'tour.title'],
        ], $activeColumns),
        'fields' => array_merge([
            'question' => ['label' => 'Question', 'type' => 'text', 'required' => true, 'max' => 500],
            'answer' => ['label' => 'Answer', 'type' => 'textarea', 'required' => true, 'max' => 3000, 'rows' => 6],
            'scope' => ['label' => 'Where it appears', 'type' => 'select', 'options' => FaqScope::class, 'required' => true, 'default' => 'global', 'hint' => 'Global FAQs show on the home page. Tour FAQs show on that tour only.'],
            'tour_id' => [
                'label' => 'Tour',
                'type' => 'select',
                'options_from' => ['model' => Tour::class, 'label' => 'title'],
                'blank' => 'No tour (global FAQ)',
                'rules' => ['required_if:scope,tour', 'nullable', 'integer', 'exists:tours,id'],
                'hint' => 'Needed only when "Where it appears" is Tour.',
            ],
        ], $display),
    ],

    'blogs' => [
        'model' => Blog::class,
        'singular' => 'Blog post',
        'plural' => 'Blog posts',
        'label_key' => 'title',
        'cover' => true,
        'search' => ['title', 'excerpt'],
        'order' => [['created_at', 'desc']],
        'with' => ['category'],
        'columns' => [
            ['label' => 'Image', 'key' => 'cover', 'type' => 'image'],
            ['label' => 'Title', 'key' => 'title'],
            ['label' => 'Category', 'key' => 'category.name'],
            ['label' => 'Status', 'key' => 'status', 'type' => 'enum', 'colors' => ['published' => 'green', 'draft' => 'amber']],
            ['label' => 'Published', 'key' => 'published_at', 'type' => 'date'],
        ],
        'fields' => array_merge([
            'title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 190],
            'slug' => $slug,
            'blog_category_id' => [
                'label' => 'Category',
                'type' => 'select',
                'options_from' => ['model' => BlogCategory::class, 'label' => 'name'],
                'blank' => 'Uncategorised',
            ],
            'excerpt' => ['label' => 'Short summary', 'type' => 'textarea', 'max' => 500, 'rows' => 3],
            'content' => ['label' => 'Content', 'type' => 'richtext', 'required' => true, 'hint' => 'Basic HTML allowed (p, h2, h3, ul, li, strong, em, a, img). Anything else is removed on save.'],
            'cover' => ['label' => 'Cover image', 'type' => 'image', 'hint' => 'JPG, PNG or WebP, up to 4 MB.'],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => BlogStatus::class, 'required' => true, 'default' => 'draft', 'section' => 'Publishing'],
            'published_at' => ['label' => 'Publish date', 'type' => 'datetime', 'section' => 'Publishing', 'hint' => 'Leave empty to publish right away when the status is Published. A future date schedules the post.'],
        ], $seo),
    ],

    'blog-categories' => [
        'model' => BlogCategory::class,
        'singular' => 'Blog category',
        'plural' => 'Blog categories',
        'label_key' => 'name',
        'toggle' => true,
        'search' => ['name'],
        'counts' => ['blogs'],
        'columns' => array_merge([
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Posts', 'key' => 'blogs_count'],
        ], $activeColumns),
        'fields' => array_merge([
            'name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 120],
            'slug' => $slug,
        ], $display),
    ],

    'coupons' => [
        'model' => Coupon::class,
        'singular' => 'Coupon',
        'plural' => 'Coupons',
        'label_key' => 'code',
        'toggle' => true,
        'search' => ['code'],
        'order' => [['created_at', 'desc']],
        'columns' => [
            ['label' => 'Code', 'key' => 'code'],
            ['label' => 'Type', 'key' => 'type', 'type' => 'enum'],
            ['label' => 'Value', 'key' => 'value'],
            ['label' => 'Used', 'key' => 'used_count'],
            ['label' => 'Expires', 'key' => 'expires_at', 'type' => 'date'],
            ['label' => 'Active', 'key' => 'is_active', 'type' => 'bool'],
        ],
        'fields' => [
            'code' => ['label' => 'Code', 'type' => 'text', 'required' => true, 'max' => 50, 'unique' => true, 'uppercase' => true, 'hint' => 'Saved in capital letters. Example: SAFARI10'],
            'type' => ['label' => 'Discount type', 'type' => 'select', 'options' => AdjustmentType::class, 'required' => true, 'default' => 'percent'],
            'value' => ['label' => 'Value', 'type' => 'number', 'required' => true, 'hint' => 'Percent: 1 to 100. Fixed: the amount taken off.'],
            'min_amount' => ['label' => 'Minimum order', 'type' => 'number', 'default' => 0, 'hint' => 'The coupon only works when the order is at least this much.'],
            'usage_limit' => ['label' => 'Usage limit', 'type' => 'number', 'integer' => true, 'hint' => 'Leave empty for unlimited use.'],
            'starts_at' => ['label' => 'Starts at', 'type' => 'datetime', 'section' => 'Validity'],
            'expires_at' => ['label' => 'Expires at', 'type' => 'datetime', 'section' => 'Validity'],
            'is_active' => ['label' => 'Active', 'type' => 'checkbox', 'default' => true, 'section' => 'Validity'],
        ],
    ],

];