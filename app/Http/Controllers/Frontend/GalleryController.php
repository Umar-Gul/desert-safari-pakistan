<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim((string) $request->input('category', ''));

        $items = GalleryItem::query()->active()->with('media')
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->ordered()
            ->paginate(24)
            ->withQueryString();

        $categories = GalleryItem::query()->active()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.gallery.index', compact('items', 'categories', 'category'));
    }
}