<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;

class ApiV1UpdateProfileRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'contact' => 'nullable|numeric|digits:10|unique:users,contact,' . $userId,
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'contact.digits' => 'Contact number must be exactly 10 digits.',
            'contact.unique' => 'This contact number is already registered to another user.',
            'profile.image' => 'Profile image must be a valid image file.',
            'profile.max' => 'Profile image size must not exceed 5MB.',
        ];
    }
}
