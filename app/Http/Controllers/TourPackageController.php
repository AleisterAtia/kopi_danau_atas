<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;

class TourPackageController extends Controller
{
    public function index()
    {
        // All reviews are auto-published — count every review on each package.
        $packages = TourPackage::where('is_active', true)
            ->with('images')
            ->withCount('reviews')
            ->latest()
            ->paginate(9);

        return view('pages.packages.index', compact('packages'));
    }

    public function show(string $slug)
    {
        // All reviews are visible publicly without moderation.
        $package = TourPackage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images', 'reviews' => fn ($q) => $q->with('user')->latest()])
            ->firstOrFail();

        return view('pages.packages.show', compact('package'));
    }
}
