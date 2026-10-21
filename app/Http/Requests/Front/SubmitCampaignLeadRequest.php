<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCampaignLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'popup_id' => 'required|exists:business_home_popups,id',
            'contact' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
            'fields' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'contact.digits' => 'Please enter a valid 10-digit phone number.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email address may not be greater than 255 characters.',
        ];
    }
}
