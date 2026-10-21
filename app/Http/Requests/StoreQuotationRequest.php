<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|integer',
            'business_customer_id' => 'nullable|integer',
            'customer_role_id' => 'nullable|exists:business_customer_roles,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_contact' => 'required|digits:10',
            'address' => 'nullable|string|max:1000',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'discount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
            'is_pf' => 'nullable|boolean',
            'packing_charge' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|date',
            'created_from' => 'nullable|string|in:pos,inquiry',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_contact.required' => 'Customer contact number is required.',
            'customer_contact.digits' => 'Customer contact number must be exactly 10 digits.',
            'items.required' => 'At least one item is required for the quotation.',
            'items.min' => 'At least one item is required for the quotation.',
            'items.*.product_id.required' => 'Each item must be a valid product.',
            'items.*.price.numeric' => 'Item price must be a valid number.',
            'items.*.qty.min' => 'Item quantity must be at least 1.',
        ];
    }
}
