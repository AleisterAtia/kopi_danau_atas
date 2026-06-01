<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;

class TourPackageController extends Controller
{
    public function index()
    {
        // Eager-load images (avoid N+1 in card thumbnails) and pre-compute
        // the reviews avg rating so the `average_rating` accessor doesn't
        // run an aggregate query per package.
        $packages = TourPackage::where('is_active', true)
            ->with('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(9);

        return view('pages.packages.index', compact('packages'));
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
