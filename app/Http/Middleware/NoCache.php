<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevent the browser (and its back/forward cache) from serving a stored copy
 * of a page. The navbar reflects auth state on every page, so without this the
 * Back button shows a cached "logged-in" page after logout until a refresh.
 * `no-store` disables Chrome's bfcache, forcing a fresh request that reflects
 * the real session state.
 */
class NoCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
