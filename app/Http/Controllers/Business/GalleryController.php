<?php

namespace App\Http\Controllers\Business;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = Auth::user()->business_id;
        $galleries = Gallery::where('business_id', $business_id)->latest()->get();
        return view('business.gallery.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('business.gallery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.gallery.index');

        try {
            $rules = [
                'title' => 'required|string|max:255',
                'type' => 'required|in:image,video,doc',
                'status' => 'required',
            ];

            if ($request->type == 'image') {
                $rules['file'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
            } elseif ($request->type == 'video') {
                $rules['link'] = ['required', 'url', 'regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/.*$/'];
            } else {
                $rules['link'] = 'required|url';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $image_url = '';
                if ($request->type == 'image' && $request->hasFile('file')) {
                    $image_url = fileUploadStorage($request->file('file'), 'gallery', 800, 600);
                } else {
                    $image_url = $request->link;
                }

                Gallery::create([
                    'business_id' => Auth::user()->business_id,
                    'title' => $request->title,
                    'type' => $request->type,
                    'image_url' => $image_url,
                    'status' => $request->status,
                ]);

                $success = true;
                $message = 'Gallery item created successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
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
        $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'data' => $gallery]);
        }
        return view('business.gallery.edit', compact('gallery'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.gallery.index');

        try {
            $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();

            $rules = [
                'title' => 'required|string|max:255',
                'type' => 'required|in:image,video,doc',
                'status' => 'required',
            ];

            if ($request->type == 'image') {
                $rules['file'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
            } elseif ($request->type == 'video') {
                $rules['link'] = ['required', 'url', 'regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/.*$/'];
            } else {
                $rules['link'] = 'required|url';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $image_url = $gallery->image_url;
                if ($request->type == 'image') {
                    if ($request->hasFile('file')) {
                        $oldimg = $gallery->image_url;
                        $image_url = fileUploadStorage($request->file('file'), 'gallery', 800, 600);
                        
                        if (isset($oldimg) && !filter_var($oldimg, FILTER_VALIDATE_URL)) {
                            fileRemoveStorage($oldimg);
                        }
                    }
                } else {
                    // For video/doc, if it was previously an uploaded file, remove it
                    if (!filter_var($gallery->image_url, FILTER_VALIDATE_URL)) {
                        fileRemoveStorage($gallery->image_url);
                    }
                    $image_url = $request->link;
                }

                $gallery->title = $request->title;
                $gallery->type = $request->type;
                $gallery->image_url = $image_url;
                $gallery->status = $request->status;
                $gallery->save();

                $success = true;
                $message = 'Gallery item updated successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
        fileRemoveStorage($gallery->image_url);
        $gallery->delete();

        return response()->json(['success' => true, 'message' => 'Gallery item deleted successfully.']);
    }
}
