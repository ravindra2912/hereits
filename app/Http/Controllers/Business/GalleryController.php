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
        if (request()->ajax()) {
            $business_id = Auth::user()->business_id;
            $data = Gallery::where('business_id', $business_id);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    return '<img src="' . getImage($row->image_url) . '" class="rounded" style="width: 100px; height: 60px; object-fit: cover;">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 'active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('business.gallery.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGallery(' . $row->id . ')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }
        return view('business.gallery.index');
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
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'status' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $image_url = '';
                if ($request->hasFile('image')) {
                    $image_url = fileUploadStorage($request->file('image'), 'gallery', 800, 600);
                }

                Gallery::create([
                    'business_id' => Auth::user()->business_id,
                    'title' => $request->title,
                    'image_url' => $image_url,
                    'status' => $request->status,
                ]);

                $success = true;
                $message = 'Gallery image created successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_url)) {
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'status' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                if ($request->hasFile('image')) {
                    $oldimg = $gallery->image_url;
                    $newimg = fileUploadStorage($request->file('image'), 'gallery', 800, 600);
                    $gallery->image_url = $newimg;
                }

                $gallery->title = $request->title;
                $gallery->status = $request->status;
                $gallery->save();

                if (isset($oldimg)) {
                    fileRemoveStorage($oldimg);
                }

                $success = true;
                $message = 'Gallery image updated successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
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
        $gallery = Gallery::where('id', $id)->where('business_id', Auth::user()->business_id)->firstOrFail();
        fileRemoveStorage($gallery->image_url);
        $gallery->delete();

        return response()->json(['success' => true, 'message' => 'Gallery image deleted successfully.']);
    }
}
