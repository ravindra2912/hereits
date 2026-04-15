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
        $blogs = Blog::where('status', 'active')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('front.blog.index', compact('blogs'));
    }

    /**
     * Display the specified blog.
     */
    public function show($slug): View
    {
        $blog = Blog::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Fetch related blogs (excluding current one)
        $relatedBlogs = Blog::where('status', 'active')
            ->where('id', '!=', $blog->id)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('front.blog.detail', compact('blog', 'relatedBlogs'));
    }
}
