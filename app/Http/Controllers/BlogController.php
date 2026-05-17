<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $categorySlug = $request->query('category');

        // Featured: artikel terbaru (tanpa filter agar selalu ada featured)
        $featured = BlogPost::published()
            ->with('category')
            ->latest('published_at')
            ->first();

        // List artikel reguler
        $postsQuery = BlogPost::published()
            ->with('category')
            ->latest('published_at');

        if ($featured) {
            $postsQuery->where('id', '!=', $featured->id);
        }

        if ($search !== '') {
            $postsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($categorySlug) {
            $postsQuery->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $posts = $postsQuery->paginate(6)->withQueryString();

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();

        // Kategori populer (urut by jumlah post desc, ambil 6)
        $popularCategories = $categories->sortByDesc('posts_count')->take(6)->values();

        return view('pages.blog.index', compact(
            'posts',
            'categories',
            'popularCategories',
            'featured',
            'search',
            'categorySlug'
        ));
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->published()
            ->with('category')
            ->firstOrFail();

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->take(3)
            ->get();

        return view('pages.blog.show', compact('post', 'relatedPosts'));
    }
}
