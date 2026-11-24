<?php

namespace App\Http\Requests\Business\Inquiry;

use Illuminate\Foundation\Http\FormRequest;

class RejectInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks' => 'nullable|string|max:500',
        ];
    }
}

