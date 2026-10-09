<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('frontend.blog.index', [
            'category' => null,
            'posts' => $this->posts()->paginate(9),
            'categories' => $this->categories(),
        ]);
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::query()->active()->where('slug', $slug)->firstOrFail();

        return view('frontend.blog.index', [
            'category' => $category,
            'posts' => $this->posts()->where('blog_category_id', $category->id)->paginate(9),
            'categories' => $this->categories(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Blog::query()->published()->where('slug', $slug)->with(['media', 'category'])->firstOrFail();

        $related = Blog::query()->published()->with(['media', 'category:id,name,slug'])
            ->whereKeyNot($post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'related'));
    }

    private function posts()
    {
        return Blog::query()->published()->with(['media', 'category:id,name,slug'])->latest('published_at');
    }

    private function categories()
    {
        return BlogCategory::query()->active()->ordered()
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->get();
    }
}