<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = getBusinessId();

        return [
            'customer_name' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'contact' => 'nullable|digits:10',
            'customer_role_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('business_customer_roles', 'id')->where(function ($query) use ($businessId) {
                    $query->where('business_id', $businessId);
                }),
            ],
            'address' => 'nullable|string|max:1000',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'contact.digits' => 'Contact number must be exactly 10 digits.',
            'customer_role_id.exists' => 'The selected customer group is invalid.',
        ];
    }
}
