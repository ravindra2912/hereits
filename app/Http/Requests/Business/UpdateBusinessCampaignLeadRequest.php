<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessCampaignLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'popup_id' => 'nullable|exists:business_home_popups,id',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:new,contacted,scheduled,in_progress,converted,closed_lost,duplicate,spam',
            'scheduled_at' => 'nullable|date',
            'details' => 'nullable|array',
        ];
    }
}
