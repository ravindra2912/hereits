<?php

namespace App\Http\Requests\Chat;

use App\Services\ChatService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $participants = $this->input('participants', []);

        if (is_string($participants)) {
            $decoded = json_decode($participants, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $participants = $decoded;
            }
        }

        $this->merge([
            'conversation_type' => $this->input('conversation_type', ChatService::CONVERSATION_DIRECT),
            'title' => trim((string) $this->input('title', '')) ?: null,
            'participants' => is_array($participants) ? $participants : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'conversation_type' => ['required', Rule::in([ChatService::CONVERSATION_DIRECT, ChatService::CONVERSATION_GROUP])],
            'title' => ['nullable', 'string', 'max:255'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.type' => ['required', Rule::in([ChatService::PARTICIPANT_USER, ChatService::PARTICIPANT_BUSINESS])],
            'participants.*.id' => ['required', 'integer', 'min:1'],
        ];
    }
}