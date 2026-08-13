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

        // Eager-load images (avoid N+1 in card thumbnails), pre-compute the
        // reviews avg rating so the `average_rating` accessor doesn't run an
        // aggregate query per package, and precompute today's booked guest
        // count so cards can show remaining slots without extra queries.
        $query = TourPackage::where('is_active', true)
            ->with(['images', 'category'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withSum(['bookings as today_booked_guests' => fn ($q) => $q->whereDate('visit_date', today())->occupyingQuota()], 'guest_count');

        if ($search !== '') {
            // name/description are translatable JSON columns, which MySQL
            // stores with a case-sensitive (utf8mb4_bin) collation regardless
            // of the table's default — a plain LIKE only matched the exact
            // case the guest typed. LOWER() on both sides makes it
            // case-insensitive like every other filter on this page.
            $needle = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$needle]);
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

        return view('pages.packages.index', compact(
            'packages',
            'categories',
            'search',
            'categorySlugs',
            'priceMin',
            'priceMax',
            'sort'
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

        return view('pages.packages.show', compact('package'));
    }
}
