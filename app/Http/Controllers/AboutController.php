<?php

namespace App\Http\Controllers;

use App\Models\CoffeeVariety;
use App\Models\CompanyValue;
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
        // manage one image pool, not two. Capped so an admin uploading hundreds
        // of photos doesn't balloon page weight; the view rotates through this
        // set 8 at a time instead of showing everything at once.
        $gallery = HomepageImage::orderBy('sort_order')->take(40)->get();

        $map = SiteSetting::map();
        $settings = [
            'company_name' => $map['company_name'] ?? 'CV Kopi Danau Diatas',
            'company_address' => $map['company_address'] ?? null,
            'company_phone' => $map['company_phone'] ?? null,
            'company_whatsapp' => $map['company_whatsapp'] ?? null,
            'company_email' => $map['company_email'] ?? null,
            'google_maps_embed' => $map['google_maps_embed'] ?? null,
            'instagram_url' => $map['instagram_url'] ?? null,
            'facebook_url' => $map['facebook_url'] ?? null,
            'tiktok_url' => $map['tiktok_url'] ?? null,
        ];

        // Editable via Filament > Site Settings (keys: about_altitude_mdpl,
        // about_vision, about_mission — mission is one line per item).
        // Fall back to the original copy when admin hasn't set them.
        $altitude = $map['about_altitude_mdpl'] ?? '1.400';
        $vision = $map['about_vision'] ?? __('Menjadi rujukan agrowisata kopi Arabika Sumatera Barat — tempat cita rasa kopi Solok, kesejahteraan petani, dan kelestarian dataran tinggi tumbuh beriringan.');
        $mission = ! empty(trim($map['about_mission'] ?? ''))
            ? preg_split('/\r?\n/', trim($map['about_mission']))
            : [
                __('Menghadirkan wisata edukasi kopi yang jujur, dari petik ceri merah hingga seruput terakhir.'),
                __('Membudidayakan Arabika unggulan secara berkelanjutan di ketinggian Danau Diatas.'),
                __('Memberdayakan petani kopi lokal lewat kemitraan yang adil.'),
                __('Merawat tanah, air, dan hutan dataran tinggi sebagai warisan bersama.'),
            ];

        $values = CompanyValue::orderBy('sort_order')->get();

        return view('pages.about', compact('about', 'varieties', 'gallery', 'settings', 'altitude', 'vision', 'mission', 'values'));
    }
}
