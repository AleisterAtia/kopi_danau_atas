<?php

namespace App\Http\Controllers;

use App\Models\HomepageSection;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\TourPackage;

class HomeController extends Controller
{
    public function index()
    {
        // HomepageSection::findByKey is now backed by per-key cache and
        // eagerly loads `images`, so the homepage's 4 section lookups
        // become 0 queries on the cached path and 4 queries (each with
        // its images joined) on the cold path.
        $hero = HomepageSection::findByKey('hero');
        $about = HomepageSection::findByKey('about');
        $education = HomepageSection::findByKey('education');
        $testimonials = HomepageSection::findByKey('testimonials');

        // Featured packages — eager-load images, precompute today's
        // booked count + average rating so the section card can show
        // remaining slots and rating without extra queries.
        $featuredPackages = TourPackage::where('is_active', true)
            ->where('is_featured', true)
            ->with('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount(['bookings as today_booked' => function ($q) {
                $q->whereDate('visit_date', today())
                    ->where(function ($inner) {
                        $inner->whereIn('status', ['paid', 'confirmed', 'completed'])
                            ->orWhere(function ($p) {
                                $p->where('status', 'pending')
                                    ->where('created_at', '>=', now()->subHour());
                            });
                    });
            }])
            ->take(4)
            ->get();

        // Reviews are auto-published, so no status filter is needed.
        $approvedReviews = Review::with(['user', 'tourPackage'])
            ->latest()
            ->take(6)
            ->get();

        // SiteSetting::map() is cached forever and busted by the
        // observer on save/delete, so the 9 lookups below cost a single
        // DB read on cold start and zero on subsequent renders.
        $settingsMap = SiteSetting::map();
        $settings = [
            'company_name' => $settingsMap['company_name'] ?? 'CV Kopi Danau Diatas',
            'company_address' => $settingsMap['company_address'] ?? null,
            'company_phone' => $settingsMap['company_phone'] ?? null,
            'company_email' => $settingsMap['company_email'] ?? null,
            'company_whatsapp' => $settingsMap['company_whatsapp'] ?? null,
            'google_maps_embed' => $settingsMap['google_maps_embed'] ?? null,
            'instagram_url' => $settingsMap['instagram_url'] ?? null,
            'facebook_url' => $settingsMap['facebook_url'] ?? null,
            'tiktok_url' => $settingsMap['tiktok_url'] ?? null,
        ];

        return view('pages.home', compact(
            'hero', 'about', 'education', 'testimonials',
            'featuredPackages', 'approvedReviews', 'settings'
        ));
    }
}
