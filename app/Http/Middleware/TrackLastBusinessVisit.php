<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Business;

class TrackLastBusinessVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $business_slug = $request->route('business_slug');

        if ($business_slug) {
            $lastBusiness = json_decode($request->cookie('last_visited_business'), true);

            if (!$lastBusiness || $lastBusiness['slug'] !== $business_slug) {
                // To avoid querying Database on every sub-page (services, products), 
                // we can just check if we are on a route that gives us the business object.
                // But for simplicity and correctness (in case slug is invalid), we check once.

                $business = Business::where('slug', $business_slug)->first();
                if ($business) {
                    $cookieData = [
                        'slug' => $business->slug,
                        'name' => $business->name
                    ];
                    // Set cookie for 30 days
                    $response->cookie('last_visited_business', json_encode($cookieData), 60 * 24 * 30);
                }
            }
        }

        return $response;
    }
}
