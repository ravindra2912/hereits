<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessHomePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->popup_type !== 'media') {
            $this->merge([
                'media_type' => null,
                'youtube_url' => null,
            ]);
        }

        if ($this->popup_type !== 'input_form') {
            $this->merge([
                'is_web_hook_for_google_sheet' => 0,
                'google_sheet_webhook_url' => null,
            ]);
        } else {
            $this->merge([
                'is_web_hook_for_google_sheet' => $this->has('is_web_hook_for_google_sheet') ? (bool)$this->is_web_hook_for_google_sheet : 0,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'popup_type' => 'required|in:media,content,image_with_content,input_form',
            'media_type' => 'required_if:popup_type,media|nullable|in:image,youtube',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'image_url' => 'nullable|string|max:2048',
            'youtube_url' => [
                'nullable',
                'required_if:media_type,youtube',
                'url',
                'max:2048',
                'regex:%^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com/(?:embed/|v/|watch\?v=|watch\?.+&v=|shorts/))([\w-]{11})(?:\S+)?$%'
            ],
            'description' => 'nullable|string',
            'redirect_url' => 'nullable|url|max:2048',
            'trigger_delay_seconds' => 'required|integer|min:0',
            'display_frequency' => 'required|in:daily,weekly,monthly,yearly,custom_days',
            'frequency_custom_days' => 'nullable|required_if:display_frequency,custom_days|integer|min:1',
            'dismiss_behavior' => 'nullable|in:repeat,none',
            'repeat_interval_seconds' => 'nullable|required_if:dismiss_behavior,repeat|integer|min:1',
            'status' => 'required|in:active,inactive',
            'form_input_fields' => 'nullable|array',
            'form_input_fields.*.label' => 'nullable|string|max:255',
            'form_input_fields.*.type' => 'nullable|string|in:text,email,phone,number,textarea,select',
            'form_input_fields.*.placeholder' => 'nullable|string|max:255',
            'form_input_fields.*.required' => 'nullable|boolean',
            'is_web_hook_for_google_sheet' => 'nullable|boolean',
            'google_sheet_webhook_url' => 'nullable|required_if:is_web_hook_for_google_sheet,1|url|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'youtube_url.regex' => 'The YouTube URL format is invalid. Please provide a valid YouTube watch, shorts, or embed link.',
        ];
    }
}
