<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return checkBusinessPermission('add_service_category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessId = getBusinessId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId)
                        ->where('type', 'Services')
                        ->whereNull('deleted_at');
                }),
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId)
                        ->where('type', 'Services')
                        ->whereNull('deleted_at');
                }),
            ],
            'status' => 'required|in:active,in-active',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.unique' => 'This service category name already exists.',
            'parent_id.exists' => 'The selected parent category is invalid.',
            'status.required' => 'The status field is required.',
        ];
    }
}
