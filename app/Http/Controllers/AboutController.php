<?php

namespace App\Http\Controllers;

use App\Models\CoffeeVariety;
use App\Models\HomepageSection;

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

        return view('pages.about', compact('about', 'varieties'));
    }
}
