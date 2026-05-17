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
        $hero = HomepageSection::findByKey('hero');
        $about = HomepageSection::findByKey('about');
        $education = HomepageSection::findByKey('education');
        $testimonials = HomepageSection::findByKey('testimonials');

        $featuredPackages = TourPackage::where('is_active', true)
            ->where('is_featured', true)
            ->with('images')
            ->take(4)
            ->get();

        // Reviews are auto-published, so no status filter is needed.
        $approvedReviews = Review::with(['user', 'tourPackage'])
            ->latest()
            ->take(6)
            ->get();

        $settings = [
            'company_name' => SiteSetting::getValue('company_name', 'CV Kopi Danau Atas'),
            'company_address' => SiteSetting::getValue('company_address'),
            'company_phone' => SiteSetting::getValue('company_phone'),
            'company_email' => SiteSetting::getValue('company_email'),
            'company_whatsapp' => SiteSetting::getValue('company_whatsapp'),
            'google_maps_embed' => SiteSetting::getValue('google_maps_embed'),
            'instagram_url' => SiteSetting::getValue('instagram_url'),
            'facebook_url' => SiteSetting::getValue('facebook_url'),
            'tiktok_url' => SiteSetting::getValue('tiktok_url'),
        ];

        return view('pages.home', compact(
            'hero', 'about', 'education', 'testimonials',
            'featuredPackages', 'approvedReviews', 'settings'
        ));
    }
}
