<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;

class ApiV1UpdatePasswordRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|different:current_password|same:confirm_new_password',
            'confirm_new_password' => 'required|string|min:6',
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
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.different' => 'New password must be different from current password.',
            'new_password.same' => 'New password and confirmation password do not match.',
            'confirm_new_password.required' => 'Please confirm your new password.',
        ];
    }
}
