<?php

namespace App\Http\Requests\Business\Inquiry;

use Illuminate\Foundation\Http\FormRequest;

class AssignInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => 'nullable|integer|exists:users,id',
            'remarks'     => 'nullable|string|max:500',
        ];
    }
}

