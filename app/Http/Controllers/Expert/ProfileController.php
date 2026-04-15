<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private function getExpert()
    {
        return Auth::guard('expert')->user();
    }

    public function edit()
    {
        try {
            $expert = $this->getExpert();
            return view('expert.profile.edit', compact('expert'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $expert = $this->getExpert();

        $rules = [
            'expert_name' => 'required|string|max:255',
            'email' => 'required|email|unique:experts,email,' . $expert->id,
            'expert_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'password' => 'nullable|min:6|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()]);
        }

        try {
            DB::beginTransaction();

            $old_image = null;
            if ($request->hasFile('expert_image')) {
                $old_image = $expert->expert_image;
                $image_name = fileUploadStorage($request->file('expert_image'), 'expert_image', 500, 500);
                $expert->expert_image = $image_name;
            }

            $expert->expert_name = $request->expert_name;
            $expert->email = $request->email;
            $expert->title = $request->title;
            $expert->description = $request->description;

            if ($request->filled('password')) {
                $expert->password = Hash::make($request->password);
            }

            $expert->save();



            if (isset($old_image) && $old_image) {
                fileRemoveStorage($old_image);
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
        } catch (\Exception $e) {
            if (isset($image_name) && $image_name) {
                fileRemoveStorage($image_name);
            }
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
