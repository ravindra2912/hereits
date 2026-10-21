<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;

class ApiV1ResetPasswordRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6',
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
            'email.required' => 'Email address is required.',
            'email.exists' => 'No user found with this email address.',
            'token.required' => 'Password reset token is required.',
            'password.required' => 'Password is required.',
            'password.same' => 'Password and confirmation password do not match.',
            'confirm_password.required' => 'Confirm password is required.',
        ];
    }
}
