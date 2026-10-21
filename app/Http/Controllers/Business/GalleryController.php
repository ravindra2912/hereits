<?php

namespace App\Http\Controllers\Business;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\CacheManagement;

class GalleryController extends Controller
{
    use CacheManagement;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $business_id = Auth::user()->business_id;
        $query = Gallery::where('business_id', $business_id)
            ->select('id', 'title', 'type', 'image_url', 'status', 'created_at');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type') && in_array($request->type, ['image', 'youtube_url'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'in-active'])) {
            $query->where('status', $request->status);
        }

        $galleries = $query->latest()->paginate(20)->withQueryString();
        $totalCount = Gallery::where('business_id', $business_id)->count();

        if ($request->ajax()) {
            return view('business.gallery.partials.gallery_grid', compact('galleries', 'totalCount'))->render();
        }

        return view('business.gallery.index', compact('galleries', 'totalCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!checkBusinessPermission('add_gallery')) {
            abort(403, 'Unauthorized. You do not have permission to add gallery images.');
        }

        $existingCount = Gallery::where('business_id', Auth::user()->business_id)->count();
        if ($existingCount >= 20) {
            return redirect()->route('business.gallery.index')->with('error', 'Maximum limit of 20 media items reached.');
        }

        return view('business.gallery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_gallery')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add gallery images.'], 403);
        }

        $business_id = Auth::user()->business_id;
        $existingCount = Gallery::where('business_id', $business_id)->count();
        if ($existingCount >= 20) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum limit of 20 media items reached for gallery. Please delete an existing item to add a new one.'
            ], 422);
        }

        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.gallery.index');

        try {
            $type = $request->input('type', 'image');
            $rules = [
                'title' => 'required|string|max:255',
                'type' => 'required|in:image,youtube_url',
                'status' => 'required',
            ];

            if ($type === 'youtube_url') {
                $rules['youtube_url'] = 'required|string';
            } else {
                $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                if ($type === 'youtube_url') {
                    if (!getYoutubeId($request->youtube_url)) {
                        return response()->json(['success' => false, 'message' => ['youtube_url' => ['Please enter a valid YouTube video or shorts URL.']]], 422);
                    }
                    $image_url = $request->youtube_url;
                } else {
                    $image_url = '';
                    if ($request->hasFile('image')) {
                        $image_url = fileUploadStorage($request->file('image'), 'gallery', 800, 600);
                    }
                }

                Gallery::create([
                    'business_id' => Auth::user()->business_id,
                    'title' => $request->title,
                    'type' => $type,
                    'image_url' => $image_url,
                    'status' => $request->status,
                ]);

                $success = true;
                $message = 'Gallery ' . ($type === 'youtube_url' ? 'video' : 'image') . ' created successfully.';
                $this->clearBusinessGalleryCache(Auth::user()->business_id);
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            if (isset($image_url) && ($type ?? 'image') === 'image') {
                fileRemoveStorage($image_url);
            }
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!checkBusinessPermission('edit_gallery')) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit gallery.'], 403);
            }
            abort(403, 'Unauthorized. You do not have permission to edit gallery media.');
        }

        $gallery = Gallery::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->select('id', 'title', 'type', 'image_url', 'status')
            ->firstOrFail();

        if (request()->ajax()) {
            $isYt = ($gallery->type ?? 'image') === 'youtube_url';
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $gallery->id,
                    'title' => $gallery->title,
                    'type' => $gallery->type ?? 'image',
                    'image_url' => $gallery->image_url,
                    'status' => $gallery->status,
                    'preview_url' => $isYt
                        ? (getYoutubeThumbnail($gallery->image_url) ?: getImage(null))
                        : getImage($gallery->image_url),
                ]
            ]);
        }

        return view('business.gallery.edit', compact('gallery'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_gallery')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit gallery images.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.gallery.index');

        try {
            $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
            $type = $request->input('type', $gallery->type ?? 'image');

            $rules = [
                'title' => 'required|string|max:255',
                'type' => 'required|in:image,youtube_url',
                'status' => 'required',
            ];

            if ($type === 'youtube_url') {
                $rules['youtube_url'] = 'required|string';
            } else {
                if (($gallery->type ?? 'image') !== 'image' && !$request->hasFile('image')) {
                    $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
                } else {
                    $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
                }
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $oldType = $gallery->type ?? 'image';
                $oldUrl = $gallery->image_url;

                if ($type === 'youtube_url') {
                    if (!getYoutubeId($request->youtube_url)) {
                        return response()->json(['success' => false, 'message' => ['youtube_url' => ['Please enter a valid YouTube video or shorts URL.']]], 422);
                    }
                    $gallery->image_url = $request->youtube_url;
                    $gallery->type = 'youtube_url';

                    if ($oldType === 'image' && !empty($oldUrl)) {
                        fileRemoveStorage($oldUrl);
                    }
                } else {
                    $gallery->type = 'image';
                    if ($request->hasFile('image')) {
                        $newimg = fileUploadStorage($request->file('image'), 'gallery', 800, 600);
                        $gallery->image_url = $newimg;
                        if ($oldType === 'image' && !empty($oldUrl)) {
                            fileRemoveStorage($oldUrl);
                        }
                    }
                }

                $gallery->title = $request->title;
                $gallery->status = $request->status;
                $gallery->save();

                $success = true;
                $message = 'Gallery updated successfully.';
                $this->clearBusinessGalleryCache(Auth::user()->business_id);
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            if (isset($newimg)) {
                fileRemoveStorage($newimg);
            }
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_gallery')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete gallery images.'], 403);
        }
        $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
        if (($gallery->type ?? 'image') !== 'youtube_url') {
            fileRemoveStorage($gallery->image_url);
        }
        $gallery->delete();
        $this->clearBusinessGalleryCache(Auth::user()->business_id);

        return response()->json(['success' => true, 'message' => 'Gallery deleted successfully.']);
    }

    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_gallery')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit gallery images.'], 403);
        }
        try {
            $gallery = Gallery::where('id', $request->id)
                ->where('business_id', Auth::user()->business_id)
                ->firstOrFail();

            $gallery->status = $gallery->status == 'active' ? 'in-active' : 'active';
            $gallery->save();
            $this->clearBusinessGalleryCache(Auth::user()->business_id);

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $gallery->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }
}
