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
            return back()->with('error', checkResponse($e));
        }
    }

    public function update(Request $request)
    {
        $expert = $this->getExpert();

        $userId = $expert->user_id ?? 0;
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email,' . $userId,
            'expert_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'password' => 'nullable|min:6|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()]);
        }

        $firstName = trim((string)$request->input('first_name', ''));
        $lastName = trim((string)$request->input('last_name', ''));

        try {
            DB::beginTransaction();

            $user = $expert->user_id ? \App\Models\User::find($expert->user_id) : null;
            $old_image = null;

            if ($request->hasFile('expert_image') && $user) {
                $old_image = $user->profile;
                $image_name = fileUploadStorage($request->file('expert_image'), 'users', 500, 500);
                $user->profile = $image_name;
            }

            if ($user) {
                $user->email = $request->email;
                if (!empty($firstName)) {
                    $user->first_name = $firstName;
                    $user->last_name = $lastName;
                }
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
            }

            $expert->title = $request->title;
            $expert->description = $request->description;
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
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }
}
