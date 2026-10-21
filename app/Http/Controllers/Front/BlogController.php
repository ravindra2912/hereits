<?php

namespace App\Http\Controllers\Front;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display a listing of the blogs.
     */
    public function index(Request $request): View
    {
        $blogs = Blog::select('id', 'title', 'slug', 'image', 'short_description', 'content', 'published_at', 'created_at')
            ->where('status', 'active')
            ->orderBy('published_at', 'desc')
            ->whereNull('business_id')
            ->paginate(12);

        return view('front.blog.index', compact('blogs'));
    }

    /**
     * Display the specified blog.
     */
    public function show($slug): View
    {
        $blog = Blog::select('id', 'title', 'slug', 'image', 'content', 'short_description', 'published_at', 'created_at', 'meta_title', 'meta_description', 'meta_keywords')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Fetch related blogs (excluding current one)
        $relatedBlogs = Blog::select('id', 'title', 'slug', 'image', 'published_at', 'created_at')
            ->where('status', 'active')
            ->where('id', '!=', $blog->id)
            ->whereNull('business_id')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('front.blog.detail', compact('blog', 'relatedBlogs'));
    }
}
