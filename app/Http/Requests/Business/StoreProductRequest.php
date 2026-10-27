<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return checkBusinessPermission('add_products');
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
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId)
                        ->whereNull('deleted_at');
                }),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId)
                        ->where('type', 'Products')
                        ->whereNull('deleted_at');
                }),
            ],
            'label_id' => 'nullable|array',
            'label_id.*' => 'exists:labels,id',
            'quantity' => 'required|integer|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
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
            'name.required' => 'The product name is required.',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'SKU already exists.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 0.',
        ];
    }
}
