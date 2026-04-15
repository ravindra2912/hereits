<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Expert;
use App\Models\Business;
use App\Models\Product;
use App\Models\Service;

class SitemapController extends Controller
{
    public function index()
    {
        $businesses = Business::select('id', 'slug', 'updated_at')->where('status', 'active')->get();
        $experts = Expert::select('id', 'business_id', 'slug', 'updated_at')->with('business:id,slug')->where('status', 'active')->get();
        $blogs = Blog::select('id', 'slug', 'updated_at')->where('status', 'active')->get();
        $products = Product::select('id', 'business_id', 'slug', 'updated_at')->with('business:id,slug')->where('status', 'active')->get();
        $services = Service::select('id', 'business_id', 'slug', 'updated_at')->with('business:id,slug')->where('status', 'active')->get();

        return response()->view('sitemap', compact('businesses', 'experts', 'blogs', 'products', 'services'))->header('Content-Type', 'application/xml');
    }
}
