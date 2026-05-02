<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;

class TourPackageController extends Controller
{
    public function index()
    {
        $packages = TourPackage::where('is_active', true)
            ->with('images')
            ->withCount(['reviews' => fn ($q) => $q->where('status', 'approved')])
            ->latest()
            ->paginate(9);

        return view('pages.packages.index', compact('packages'));
    }

    public function show(string $slug)
    {
        $package = TourPackage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images', 'reviews' => fn ($q) => $q->where('status', 'approved')->with('user')->latest()])
            ->firstOrFail();

        return view('pages.packages.show', compact('package'));
    }
}
