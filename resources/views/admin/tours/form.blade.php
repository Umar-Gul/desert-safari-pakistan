@php
    $isEdit = $tour->exists;
    $hasOld = session()->hasOldInput();

    $val = fn(string $key, $default = '') => old($key, $isEdit ? $tour->{$key} : $default);
    $chk = fn(string $key, bool $default = false): bool => $hasOld
        ? (bool) old($key)
        : ($isEdit
            ? (bool) $tour->{$key}
            : $default);

    $in = 'mt-1 block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500';
    $lbl = 'block text-sm font-medium text-stone-700';

    $tabs = [
        'general' => 'General',
        'pricing' => 'Pricing',
        'content' => 'Details',
        'highlights' => 'Main Attractions',
        'itinerary' => 'Tour Plan',
        'included' => 'What\'s Included',
        'info' => 'Good to Know',
        'options' => 'Extras & Choices',
        'faqs' => 'Questions & Answers',
        'images' => 'Photos',
        'seo' => 'Google Search',
    ];

    $selCategories = array_map('intval', old('category_ids', $isEdit ? $tour->categories->pluck('id')->all() : []));
    $selBadges = old('badges', $isEdit ? $tour->badges?->map(fn($b) => $b->value)->all() ?? [] : []);
    $selAddons = array_map('intval', old('addon_ids', $isEdit ? $tour->addons->pluck('id')->all() : []));
    $selCamps = array_map('intval', old('camp_type_ids', $isEdit ? $tour->campTypes->pluck('id')->all() : []));
    $selVehicles = array_map('intval', old('vehicle_ids', $isEdit ? $tour->vehicles->pluck('id')->all() : []));

    $cover = $isEdit ? $tour->getFirstMedia('cover') : null;
    $gallery = $isEdit ? $tour->getMedia('gallery') : collect();
@endphp

<x-admin.layout :title="$isEdit ? 'Edit tour' : 'New tour'">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $isEdit ? 'Edit: ' . $tour->title : 'New tour' }}</h1>
        <a href="{{ route('admin.tours.index') }}" class="text-sm text-stone-600 hover:text-amber-700">&larr; All
            tours</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-medium">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" x-data="{ tab: 'general' }"
        action="{{ $isEdit ? route('admin.tours.update', $tour->id) : route('admin.tours.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-4 flex flex-wrap gap-1 rounded-lg bg-white p-2 shadow-sm">
            @foreach ($tabs as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-amber-600 text-white' : 'text-stone-700 hover:bg-stone-100'"
                    class="rounded-md px-3 py-1.5 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        <div class="rounded-lg bg-white p-6 shadow-sm">

            {{-- General --}}
            <section x-show="tab === 'general'" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="{{ $lbl }}" for="title">Title *</label>
                        <input id="title" name="title" type="text" value="{{ $val('title') }}"
                            class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="slug">URL slug</label>
                        <input id="slug" name="slug" type="text" value="{{ $val('slug') }}"
                            class="{{ $in }}">
                        <p class="mt-1 text-xs text-stone-500">Leave blank to generate from the title (or keep the
                            current one).</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="category_id">Main category *</label>
                        <select id="category_id" name="category_id" class="{{ $in }}">
                            <option value="">Choose...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) $val('category_id') === (string) $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="desert_id">Desert</label>
                        <select id="desert_id" name="desert_id" class="{{ $in }}">
                            <option value="">None</option>
                            @foreach ($deserts as $desert)
                                <option value="{{ $desert->id }}" @selected((string) $val('desert_id') === (string) $desert->id)>{{ $desert->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="duration_hours">Duration (hours)</label>
                        <input id="duration_hours" name="duration_hours" type="number" step="0.5" min="0"
                            value="{{ $val('duration_hours', 0) }}" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="max_group_size">Max group size</label>
                        <input id="max_group_size" name="max_group_size" type="number" min="1"
                            value="{{ $val('max_group_size') }}" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="min_age">Minimum age</label>
                        <input id="min_age" name="min_age" type="number" min="0"
                            value="{{ $val('min_age') }}" class="{{ $in }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $lbl }}" for="short_description">Short description</label>
                        <textarea id="short_description" name="short_description" rows="2" class="{{ $in }}">{{ $val('short_description') }}</textarea>
                    </div>
                </div>

                <div>
                    <p class="{{ $lbl }}">Also show in these categories</p>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @foreach ($categories as $category)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                    @checked(in_array($category->id, $selCategories, true)) class="rounded border-stone-300 text-amber-600">
                                {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="{{ $lbl }}">Shown on tour card (tick what is included)</p>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @foreach (['has_dinner' => 'Dinner', 'has_breakfast' => 'Breakfast', 'has_guide' => 'Tour guide', 'has_transfer' => 'Pick & drop', 'has_itinerary' => 'Tour plan'] as $key => $label)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="{{ $key }}" value="1"
                                    @checked($chk($key)) class="rounded border-stone-300 text-amber-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="{{ $lbl }}">Badges</p>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @foreach (\App\Enums\TourBadge::cases() as $badge)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="badges[]" value="{{ $badge->value }}"
                                    @checked(in_array($badge->value, $selBadges, true)) class="rounded border-stone-300 text-amber-600">
                                {{ $badge->label() }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-stone-100 pt-4 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1"
                            @checked($chk('is_active', true)) class="rounded border-stone-300 text-amber-600"> Active
                        (visible on the site)</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1"
                            @checked($chk('is_featured')) class="rounded border-stone-300 text-amber-600">
                        Featured</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_best_selling"
                            value="1" @checked($chk('is_best_selling'))
                            class="rounded border-stone-300 text-amber-600"> Best selling</label>
                    <label class="flex items-center gap-2">Sort order
                        <input name="sort_order" type="number" min="0" value="{{ $val('sort_order', 0) }}"
                            class="w-24 rounded-md border-stone-300 text-sm">
                    </label>
                </div>
            </section>

            {{-- Pricing --}}
            <section x-show="tab === 'pricing'" x-cloak x-data="{ adult: {{ (float) $val('base_price_adult', 0) }}, child: {{ (float) $val('base_price_child', 0) }}, disc: {{ (int) $val('discount_percent', 0) }} }" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="{{ $lbl }}" for="base_price_adult">Adult price *</label>
                        <input id="base_price_adult" name="base_price_adult" type="number" step="0.01"
                            min="0" x-model.number="adult" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="base_price_child">Child price</label>
                        <input id="base_price_child" name="base_price_child" type="number" step="0.01"
                            min="0" x-model.number="child" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="discount_percent">Discount (%)</label>
                        <input id="discount_percent" name="discount_percent" type="number" min="0"
                            max="100" x-model.number="disc" class="{{ $in }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}" for="price_label">Price label *</label>
                        <input id="price_label" name="price_label" type="text"
                            value="{{ $val('price_label', 'per person') }}" class="{{ $in }}">
                    </div>
                </div>
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900">
                    Shown to customers:
                    adult <strong x-text="Math.round(adult * (100 - disc)) / 100"></strong>,
                    child <strong x-text="Math.round(child * (100 - disc)) / 100"></strong>.
                    Final prices are always calculated from these values, never stored.
                </div>
            </section>

            {{-- Content --}}
            <section x-show="tab === 'content'" x-cloak class="space-y-6">
                <div>
                    <p class="{{ $lbl }} mb-1">About this tour</p>
                    <x-admin.rich-text name="description" :value="old('description', $isEdit ? $tour->description : '')" />
                </div>
                <div>
                    <p class="{{ $lbl }} mb-1">Cancellation & Refund (this tour)</p>
                    <x-admin.rich-text name="cancellation_policy" :value="old('cancellation_policy', $isEdit ? $tour->cancellation_policy : '')" />
                </div>
            </section>

            {{-- Highlights --}}
            <section x-show="tab === 'highlights'" x-cloak>
                <p class="mb-3 text-sm text-stone-500">Short points customers see first, e.g. "Pick and drop by SUV".
                </p>
                <x-admin.repeater name="highlights" :rows="old('highlights', $state['highlights'])" add-label="Add attraction" :fields="[
                    ['key' => 'text', 'label' => 'Attraction'],
                    ['key' => 'icon', 'label' => 'Icon (optional)', 'class' => 'w-40'],
                ]" />
            </section>

            {{-- Itinerary --}}
            <section x-show="tab === 'itinerary'" x-cloak>
                <x-admin.repeater name="itinerary" :rows="old('itinerary', $state['itinerary'])" add-label="Add step" :fields="[
                    ['key' => 'time_label', 'label' => 'Time', 'class' => 'w-32'],
                    ['key' => 'title', 'label' => 'Title'],
                    [
                        'key' => 'description',
                        'label' => 'Description',
                        'type' => 'textarea',
                        'class' => 'min-w-[16rem] flex-1',
                    ],
                ]" />
            </section>

            {{-- Included / excluded --}}
            <section x-show="tab === 'included'" x-cloak class="grid gap-8 md:grid-cols-2">
                <div>
                    <p class="{{ $lbl }} mb-2">What is included</p>
                    <x-admin.repeater name="inclusions" :rows="old('inclusions', $state['inclusions'])" add-label="Add item" :fields="[['key' => 'text', 'label' => 'Included item']]" />
                </div>
                <div>
                    <p class="{{ $lbl }} mb-2">What is not included</p>
                    <x-admin.repeater name="exclusions" :rows="old('exclusions', $state['exclusions'])" add-label="Add item" :fields="[['key' => 'text', 'label' => 'Excluded item']]" />
                </div>
            </section>

            {{-- Additional info --}}
            <section x-show="tab === 'info'" x-cloak>
                <x-admin.repeater name="additional_infos" :rows="old('additional_infos', $state['additional_infos'])" add-label="Add section"
                    :fields="[
                        ['key' => 'title', 'label' => 'Heading', 'class' => 'w-56'],
                        [
                            'key' => 'content',
                            'label' => 'Text',
                            'type' => 'textarea',
                            'class' => 'min-w-[16rem] flex-1',
                        ],
                    ]" />
            </section>

            {{-- Options --}}
            <section x-show="tab === 'options'" x-cloak class="space-y-8">
                <div>
                    <p class="{{ $lbl }} mb-2">Extras <span class="font-normal text-stone-500">(special
                            price is optional)</span></p>
                    <div class="space-y-2">
                        @forelse ($addons as $addon)
                            @php
                                $override = old(
                                    "addon_prices.{$addon->id}",
                                    $isEdit
                                        ? $tour->addons->firstWhere('id', $addon->id)?->pivot?->price_override
                                        : null,
                                );
                            @endphp
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <label class="flex w-72 items-center gap-2">
                                    <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}"
                                        @checked(in_array($addon->id, $selAddons, true)) class="rounded border-stone-300 text-amber-600">
                                    {{ $addon->name }} <span
                                        class="text-stone-400">({{ number_format((float) $addon->price) }})</span>
                                </label>
                                <input type="number" step="0.01" min="0"
                                    name="addon_prices[{{ $addon->id }}]" value="{{ $override }}"
                                    placeholder="Special price" class="w-40 rounded-md border-stone-300 text-sm">
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">No extras yet. Create them under Catalogue &rarr;
                                Extras.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="{{ $lbl }} mb-2">Camp styles</p>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @forelse ($campTypes as $camp)
                            <label class="flex items-center gap-2"><input type="checkbox" name="camp_type_ids[]"
                                    value="{{ $camp->id }}" @checked(in_array($camp->id, $selCamps, true))
                                    class="rounded border-stone-300 text-amber-600"> {{ $camp->name }}</label>
                        @empty
                            <p class="text-sm text-stone-500">No camp styles yet.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="{{ $lbl }} mb-2">Vehicles</p>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        @forelse ($vehicles as $vehicle)
                            <label class="flex items-center gap-2"><input type="checkbox" name="vehicle_ids[]"
                                    value="{{ $vehicle->id }}" @checked(in_array($vehicle->id, $selVehicles, true))
                                    class="rounded border-stone-300 text-amber-600"> {{ $vehicle->name }}</label>
                        @empty
                            <p class="text-sm text-stone-500">No vehicles yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            {{-- FAQs --}}
            <section x-show="tab === 'faqs'" x-cloak>
                <p class="mb-3 text-sm text-stone-500">Questions shown on this tour's Questions & Answers tab. General questions are managed
                    under Home page &rarr; FAQs.</p>
                <x-admin.repeater name="faqs" :rows="old('faqs', $state['faqs'])" add-label="Add question" :fields="[
                    ['key' => 'question', 'label' => 'Question', 'class' => 'min-w-[14rem] flex-1'],
                    ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'class' => 'min-w-[16rem] flex-1'],
                ]" />
            </section>

            {{-- Images --}}
            <section x-show="tab === 'images'" x-cloak class="space-y-6">
                <div>
                    <p class="{{ $lbl }}">Cover image</p>
                    @if ($cover)
                        <img src="{{ $cover->getUrl('thumb') }}" alt=""
                            class="mt-2 h-28 rounded border border-stone-200 object-cover">
                        <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="remove_cover" value="1"
                                class="rounded border-stone-300 text-amber-600"> Remove cover
                        </label>
                    @endif
                    <input type="file" name="cover" accept="image/jpeg,image/png,image/webp"
                        class="mt-2 block w-full text-sm text-stone-600">
                    <label class="{{ $lbl }} mt-3" for="cover_alt">Alt text</label>
                    <input id="cover_alt" name="cover_alt" type="text"
                        value="{{ old('cover_alt', $cover?->getCustomProperty('alt')) }}"
                        class="{{ $in }}">
                    <p class="mt-1 text-xs text-stone-500">JPG, PNG or WebP up to 5 MB. WebP versions are created
                        automatically.</p>
                </div>

                <div>
                    <p class="{{ $lbl }}">Gallery</p>
                    @if ($gallery->isNotEmpty())
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                            @foreach ($gallery as $media)
                                <label class="block text-xs text-stone-600">
                                    <img src="{{ $media->getUrl('thumb') }}" alt=""
                                        class="h-24 w-full rounded border border-stone-200 object-cover">
                                    <span class="mt-1 flex items-center gap-1"><input type="checkbox"
                                            name="remove_gallery[]" value="{{ $media->id }}"
                                            class="rounded border-stone-300 text-red-600"> Remove</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp"
                        class="mt-3 block w-full text-sm text-stone-600">
                </div>
            </section>

            {{-- SEO --}}
            <section x-show="tab === 'seo'" x-cloak class="grid gap-4">
                <div>
                    <label class="{{ $lbl }}" for="meta_title">Google title</label>
                    <input id="meta_title" name="meta_title" type="text" value="{{ $val('meta_title') }}"
                        class="{{ $in }}">
                </div>
                <div>
                    <label class="{{ $lbl }}" for="meta_description">Google description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" class="{{ $in }}">{{ $val('meta_description') }}</textarea>
                </div>
                <div>
                    <label class="{{ $lbl }}" for="canonical">Main page link (advanced, leave blank)</label>
                    <input id="canonical" name="canonical" type="url" value="{{ $val('canonical') }}"
                        class="{{ $in }}">
                </div>
                <div>
                    <label class="{{ $lbl }}" for="og_image">WhatsApp / Facebook share image link</label>
                    <input id="og_image" name="og_image" type="url" value="{{ $val('og_image') }}"
                        class="{{ $in }}">
                </div>
            </section>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.tours.index') }}"
                class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Cancel</a>
            <button type="submit"
                class="rounded-md bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700">{{ $isEdit ? 'Save tour' : 'Create tour' }}</button>
        </div>
    </form>
</x-admin.layout>