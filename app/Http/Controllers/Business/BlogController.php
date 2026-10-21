<?php

namespace App\Http\Controllers\Business;

use App\Models\Blog;
use App\Models\BlogImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\CacheManagement;

class BlogController extends Controller
{
    use CacheManagement;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = Auth::user()->business_id;
            $data = Blog::where('business_id', $business_id);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image) . '" class="rounded" style="width: 100px; height: 60px; object-fit: cover;">';
                })
                ->addColumn('status', function ($row) {
                    $canEdit = checkBusinessPermission('edit_blogs');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit blogs"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                             </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('edit_blogs')) {
                        $btn .= '<a href="' . route('business.blog.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>';
                    }
                    if (checkBusinessPermission('delete_blogs')) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBlog(' . $row->id . ')"><i class="bi bi-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }
        return view('business.blog.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('business.blog.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_blogs')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add blogs.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.blog.index');

        try {
            $rules = [
                'title' => 'required|string|max:255',
                'status' => 'required|in:active,in-active',
                'published_at' => 'nullable|date',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $business_id = Auth::user()->business_id;
                $slug = generateUniqueSlug(Blog::class, $request->title, 'slug', $business_id);

                $blog = Blog::create([
                    'business_id' => $business_id,
                    'title' => $request->title,
                    'slug' => $slug,
                    'status' => $request->status,
                    'published_at' => $request->published_at ? \Carbon\Carbon::parse($request->published_at) : now(),
                ]);

                $success = true;
                $message = 'Blog created successfully.';
                $redirect = route('business.blog.edit', $blog->id);
                $this->clearBusinessBlogCache($business_id);
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!checkBusinessPermission('edit_blogs')) {
            abort(403, 'Unauthorized. You do not have permission to edit blogs.');
        }
        $business_id = Auth::user()->business_id;
        $blog = Blog::with(['images' => function ($query) {
            $query->orderBy('id', 'desc');
        }])->where('id', $id)->where('business_id', $business_id)->firstOrFail();

        return view('business.blog.edit', compact('blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_blogs')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit blogs.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.blog.index');

        try {
            $business_id = Auth::user()->business_id;
            $blog = Blog::where('id', $id)->where('business_id', $business_id)->firstOrFail();

            $rules = [
                'title' => 'required|string|max:255',
                'short_description' => 'nullable|string|max:1000',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'status' => 'required|in:active,in-active',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $old_image = $blog->image;
                $new_image = $old_image;

                if ($request->hasFile('image')) {
                    $new_image = fileUploadStorage($request->file('image'), 'blog', 800, 600);
                    $blog->image = $new_image;
                }

                $blog->title = $request->title;
                $blog->short_description = $request->short_description;
                $blog->content = $request->content;
                $blog->status = $request->status;
                $blog->published_at = $request->published_at ? \Carbon\Carbon::parse($request->published_at) : $blog->published_at;
                $blog->meta_title = $request->meta_title;
                $blog->meta_description = $request->meta_description;
                $blog->meta_keywords = $request->meta_keywords;
                $blog->save();

                // 1. Extract all image paths currently referenced in the updated content
                $usedImages = [];
                if (!empty($request->content)) {
                    preg_match_all('/(?:storage\/)?(blog_images\/[A-Za-z0-9_\-\.]+\.(?:webp|jpg|jpeg|png|gif|svg))/i', $request->content, $matches);
                    if (!empty($matches[1])) {
                        $usedImages = array_unique($matches[1]);
                    }
                }

                // 2. Associate newly uploaded or unassigned images that are present in the content
                if (!empty($usedImages)) {
                    BlogImage::where('business_id', $business_id)
                        ->where(function ($q) use ($blog) {
                            $q->whereNull('blog_id')->orWhere('blog_id', $blog->id);
                        })
                        ->whereIn('image', $usedImages)
                        ->update(['blog_id' => $blog->id]);
                }

                // 3. Forcibly delete any images belonging to this blog that are no longer used in content
                $unusedImages = BlogImage::where('business_id', $business_id)
                    ->where('blog_id', $blog->id)
                    ->whereNotIn('image', $usedImages)
                    ->get();

                foreach ($unusedImages as $unusedImage) {
                    if (!empty($unusedImage->image)) {
                        fileRemoveStorage($unusedImage->image);
                    }
                    $unusedImage->forceDelete();
                }

                if ($request->hasFile('image') && !empty($old_image)) {
                    fileRemoveStorage($old_image);
                }

                $success = true;
                $message = 'Blog updated successfully.';
                $this->clearBusinessBlogCache($business_id);
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            if (isset($new_image) && $new_image !== $old_image) {
                fileRemoveStorage($new_image);
            }
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_blogs')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete blogs.'], 403);
        }
        $business_id = Auth::user()->business_id;
        $blog = Blog::with('images')->where('id', $id)->where('business_id', $business_id)->firstOrFail();

        // Clean up main cover image
        if (!empty($blog->image)) {
            fileRemoveStorage($blog->image);
        }

        // Clean up associated content blog images
        foreach ($blog->images as $blogImage) {
            if (!empty($blogImage->image)) {
                fileRemoveStorage($blogImage->image);
            }
            $blogImage->delete();
        }

        $blog->delete();
        $this->clearBusinessBlogCache($business_id);

        return response()->json(['success' => true, 'message' => 'Blog deleted successfully.']);
    }

    /**
     * Toggle status.
     */
    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_blogs')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit blogs.'], 403);
        }
        try {
            $business_id = Auth::user()->business_id;
            $blog = Blog::where('id', $request->id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            $blog->status = $blog->status == 'active' ? 'in-active' : 'active';
            $blog->save();
            $this->clearBusinessBlogCache($business_id);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $blog->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    /**
     * Handle editor image upload and store in blog_images table
     */
    public function uploadImage(Request $request)
    {
        if (!checkBusinessPermission('edit_blogs') && !checkBusinessPermission('add_blogs')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to upload blog images.'], 403);
        }
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'blog_id' => 'nullable|integer',
        ]);

        $url = '';
        $blogImage = null;
        $business_id = Auth::user()->business_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $image_name = fileUploadStorage($file, 'blog_images', 1200, 800);
            $url = getImage($image_name);

            $blog_id = $request->filled('blog_id') && Blog::where('id', $request->blog_id)->where('business_id', $business_id)->exists()
                ? $request->blog_id
                : null;

            $blogImage = BlogImage::create([
                'business_id' => $business_id,
                'blog_id' => $blog_id,
                'image' => $image_name,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        return response()->json([
            'success' => true,
            'location' => $url,
            'url' => $url,
            'image_id' => $blogImage?->id,
            'filename' => $blogImage?->original_name ?? basename($url),
        ]);
    }
}
