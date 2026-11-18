<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class SendWhatsAppOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_contact' => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
            'template_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_]+$/'],
            'language' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_contact.required' => 'Contact number is required.',
            'business_contact.digits' => 'Contact number must be exactly 10 digits.',
            'business_contact.regex' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'template_name.regex' => 'Template name may only contain letters, numbers, and underscores.',
        ];
    }
}
