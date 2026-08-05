<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $packages = auth()->user()->favoritePackages()
            ->with('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('wishlists.created_at')
            ->paginate(9);

        return view('pages.wishlist.index', compact('packages'));
    }

    public function toggle(TourPackage $package)
    {
        $result = auth()->user()->favoritePackages()->toggle($package->id);

        return response()->json(['wishlisted' => count($result['attached']) > 0]);
    }
}
