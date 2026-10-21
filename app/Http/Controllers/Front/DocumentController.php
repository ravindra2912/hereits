<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Display custom domain integration documentation.
     * Business details and the proxy hash key are only loaded if an admin is logged in.
     */
    public function domainMapping(Request $request, ?string $slug = null): View
    {
        $isAdmin = Auth::guard('admin')->check() || (Auth::check() && in_array(Auth::user()->role, ['Admin', 'admin', 'superadmin']));

        $business = null;
        $proxyHashKey = 'YOUR_SECRET_HASH_KEY_HERE';

        if ($isAdmin) {
            if (!empty($slug)) {
                $business = Business::select('id', 'name', 'slug', 'business_logo', 'business_image', 'contact')
                    ->where('slug', $slug)
                    ->first();
            }

            $configuredHash = (string) config('services.proxy.hash_key', '');
            if (!empty($configuredHash)) {
                $proxyHashKey = $configuredHash;
            }
        }

        $appUrl = config('app.url');
        $targetDomain = parse_url($appUrl, PHP_URL_HOST) ?? 'hereits.com';

        return view('front.documents.domain_mapping', compact('business', 'slug', 'targetDomain', 'proxyHashKey', 'isAdmin'));
    }
}
