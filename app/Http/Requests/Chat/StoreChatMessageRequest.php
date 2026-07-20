<?php

namespace App\Http\Requests\Chat;

use App\Services\ChatService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $messageType = $this->input('message_type', ChatService::MESSAGE_TEXT);
        if ($messageType === 'inquery') {
            $messageType = ChatService::MESSAGE_INQUIRY;
        }

        $metadata = $this->input('metadata', []);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $metadata = $decoded;
            }
        }

        $this->merge([
            'message_type' => $messageType,
            'body' => trim((string) $this->input('body', '')) ?: null,
            'metadata' => is_array($metadata) ? $metadata : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'message_type' => ['required', Rule::in([
                ChatService::MESSAGE_TEXT,
                ChatService::MESSAGE_IMAGE,
                ChatService::MESSAGE_INQUIRY,
                ChatService::MESSAGE_PLACE_ORDER,
                ChatService::MESSAGE_QUOTATION,
                ChatService::MESSAGE_ORDER,
            ])],
            'body' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
            'reply_to_message_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('message_type') === ChatService::MESSAGE_IMAGE && !$this->hasFile('attachments')) {
                $validator->errors()->add('attachments', 'Please attach at least one image.');
            }

            if (in_array($this->input('message_type'), [ChatService::MESSAGE_TEXT, ChatService::MESSAGE_INQUIRY, ChatService::MESSAGE_PLACE_ORDER], true) && blank($this->input('body'))) {
                $validator->errors()->add('body', 'Message body is required.');
            }
        });
    }
}