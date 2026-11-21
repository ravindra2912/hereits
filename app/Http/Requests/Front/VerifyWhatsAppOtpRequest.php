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
            'contact' => ['required_without:business_contact', 'nullable', 'digits:10'],
            'business_contact' => ['required_without:contact', 'nullable', 'digits:10'],
            'otp' => ['required', 'digits:6'],
        ];
    }

    /**
     * Get the sanitized 10-digit contact number from input.
     */
    public function getContact(): string
    {
        return (string) ($this->input('contact') ?: $this->input('business_contact'));
    }

    public function messages(): array
    {
        return [
            'contact.required_without' => 'Contact number is required.',
            'contact.digits' => 'Contact number must be exactly 10 digits.',
            'business_contact.required_without' => 'Contact number is required.',
            'business_contact.digits' => 'Contact number must be exactly 10 digits.',
            'otp.required' => 'Verification code is required.',
            'otp.digits' => 'Verification code must be exactly 6 digits.',
        ];
    }
}
