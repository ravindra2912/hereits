<?php

namespace App\Http\Requests\Business\Inquiry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_status'   => 'required|string|in:new,contacted,follow_up,scheduled,converted,closed',
            'next_follow_up_at' => 'nullable|date',
            'remarks'           => 'nullable|string|max:500',
        ];
    }
}

