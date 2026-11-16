<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class VerifyWhatsAppOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_contact' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_contact.required' => 'Contact number is required.',
            'business_contact.digits' => 'Contact number must be exactly 10 digits.',
            'otp.required' => 'Verification code is required.',
            'otp.digits' => 'Verification code must be exactly 6 digits.',
        ];
    }
}

