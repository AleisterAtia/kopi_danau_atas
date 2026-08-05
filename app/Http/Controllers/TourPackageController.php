<?php

namespace App\Http\Controllers;

use App\Models\PackageCategory;
use App\Models\TourPackage;
use Illuminate\Http\Request;

class TourPackageController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        // Accept category as array (checkbox filter) or a single slug string
        // from legacy links; (array) cast normalises both to a list.
        $categorySlugs = array_values(array_filter((array) $request->query('category', [])));
        $priceMin = $request->query('price_min');
        $priceMax = $request->query('price_max');
        $sort = $request->query('sort', 'latest');

        // Eager-load images (avoid N+1 in card thumbnails) and pre-compute
        // the reviews avg rating so the `average_rating` accessor doesn't
        // run an aggregate query per package.
        $query = TourPackage::where('is_active', true)
            ->with(['images', 'category'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($categorySlugs)) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $categorySlugs));
        }

        if (is_numeric($priceMin)) {
            $query->where('price', '>=', (float) $priceMin);
        }

        if (is_numeric($priceMax)) {
            $query->where('price', '<=', (float) $priceMax);
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        // withQueryString keeps q/category/price/sort across pagination links.
        $packages = $query->paginate(9)->withQueryString();

        $categories = PackageCategory::orderBy('name')->get();

        // Wishlisted package IDs for the logged-in user, fetched once so each
        // card's heart icon can render its state without an N+1 query.
        $wishlistedIds = auth()->check()
            ? auth()->user()->favoritePackages()->pluck('tour_packages.id')->all()
            : [];

        return view('pages.packages.index', compact(
            'packages',
            'categories',
            'search',
            'categorySlugs',
            'priceMin',
            'priceMax',
            'sort',
            'wishlistedIds'
        ));
    }

    public function show(string $slug)
    {
        // All reviews are visible publicly without moderation.
        // withAvg + withCount on the same query as the package so the
        // detail page can display rating summary without extra round-trips.
        $package = TourPackage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images', 'reviews' => fn ($q) => $q->with('user')->latest()])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->firstOrFail();

        $isWishlisted = auth()->check()
            && auth()->user()->favoritePackages()->where('tour_package_id', $package->id)->exists();

        return view('pages.packages.show', compact('package', 'isWishlisted'));
    }
}
