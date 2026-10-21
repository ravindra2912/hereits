<?php

namespace App\Http\Requests\Api\V1\Favorite;

use App\Http\Requests\Api\BaseApiRequest;

class ApiV1ToggleFavoriteRequest extends BaseApiRequest
{
    /**
     * Prepare data for validation by normalizing keys.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('type') ?? $this->input('favorite_type');
        $itemId = $this->input('item_id') ?? $this->input('favorite_item_id');

        $this->merge([
            'favorite_type' => $type ? strtolower(trim((string)$type)) : null,
            'favorite_item_id' => $itemId !== null ? (int)$itemId : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'favorite_type' => 'required|string|in:product,service,expert,business',
            'favorite_item_id' => 'required|integer|min:1',
            'business_id' => 'nullable|integer|min:1',
            'type' => 'nullable|string',
            'item_id' => 'nullable|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'favorite_type.required' => 'The favorite type is required (product, service, expert, or business).',
            'favorite_type.in' => 'The favorite type must be one of: product, service, expert, business.',
            'favorite_item_id.required' => 'The item ID to favorite or follow is required.',
            'favorite_item_id.integer' => 'The item ID must be a valid integer.',
        ];
    }

    /**
     * Normalized favorite type.
     */
    public function getFavoriteType(): string
    {
        return (string)$this->input('favorite_type');
    }

    /**
     * Normalized item ID.
     */
    public function getFavoriteItemId(): int
    {
        return (int)$this->input('favorite_item_id');
    }

    /**
     * Resolved business ID if provided.
     */
    public function getBusinessId(): ?int
    {
        $bizId = $this->input('business_id');
        return $bizId !== null ? (int)$bizId : null;
    }
}
