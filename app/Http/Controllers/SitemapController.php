<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\TourPackage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = [
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('about'), 'priority' => '0.8'],
                ['loc' => route('packages.index'), 'priority' => '0.9'],
                ['loc' => route('blog.index'), 'priority' => '0.7'],
            ];

            foreach (TourPackage::where('is_active', true)->get() as $package) {
                $urls[] = [
                    'loc' => route('packages.show', $package->slug),
                    'lastmod' => $package->updated_at->toAtomString(),
                    'priority' => '0.8',
                ];
            }

            foreach (BlogPost::published()->get() as $post) {
                $urls[] = [
                    'loc' => route('blog.show', $post->slug),
                    'lastmod' => $post->updated_at->toAtomString(),
                    'priority' => '0.6',
                ];
            }

            return view('sitemap', compact('urls'))->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
