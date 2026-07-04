<?php

namespace App\Http\Controllers;

use App\Models\CoffeeVariety;
use App\Models\HomepageImage;
use App\Models\HomepageSection;
use App\Models\SiteSetting;

class AboutController extends Controller
{
    public function index()
    {
        // Reuse the homepage 'about' section (title/description/images) as the
        // page's company story — no extra content for admins to maintain.
        $about = HomepageSection::findByKey('about');

        $varieties = CoffeeVariety::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Existing homepage gallery doubles as the About page gallery so admins
        // manage one image pool, not two.
        $gallery = HomepageImage::orderBy('sort_order')->take(8)->get();

        $map = SiteSetting::map();
        $settings = [
            'company_name' => $map['company_name'] ?? 'CV Kopi Danau Atas',
            'company_address' => $map['company_address'] ?? null,
            'company_phone' => $map['company_phone'] ?? null,
            'company_whatsapp' => $map['company_whatsapp'] ?? null,
            'company_email' => $map['company_email'] ?? null,
            'google_maps_embed' => $map['google_maps_embed'] ?? null,
            'instagram_url' => $map['instagram_url'] ?? null,
            'facebook_url' => $map['facebook_url'] ?? null,
            'tiktok_url' => $map['tiktok_url'] ?? null,
        ];

        return view('pages.about', compact('about', 'varieties', 'gallery', 'settings'));
    }
}
