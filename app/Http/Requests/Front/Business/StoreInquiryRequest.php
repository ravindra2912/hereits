<?php

namespace App\Http\Requests\Front\Business;

use App\Models\Business;
use App\Models\BusinessInquiry;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInquiryRequest extends FormRequest
{
    protected ?Business $businessModel = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retrieve and cache the active Business model for this request.
     */
    public function getBusiness(): Business
    {
        if ($this->businessModel === null) {
            $slug = $this->route('business_slug');
            $this->businessModel = Business::select('id', 'slug', 'owner_id', 'name')
                ->where('slug', $slug)
                ->where('status', 'active')
                ->firstOrFail();
        }

        return $this->businessModel;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $business = $this->getBusiness();
        $setting = getBusinessSettings($business->id);

        if (!empty($setting->is_login_required) && !auth()->check()) {
            throw new HttpResponseException(response()->json([
                'status'         => 'error',
                'requires_login' => true,
                'message'        => 'Please login to continue with your inquiry.',
            ], 401));
        }

        if (auth()->check()) {
            $user = auth()->user();
            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

            $this->merge([
                'name'    => $this->filled('name') ? $this->input('name') : ($fullName ?: 'Customer'),
                'contact' => $this->filled('contact') ? $this->input('contact') : ($user->contact ?: ''),
                'email'   => $this->filled('email') ? $this->input('email') : ($user->email ?: null),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:150'],
            'contact'               => ['required', 'string', 'max:20'],
            'email'                 => ['nullable', 'email', 'max:150'],
            'subject'               => ['nullable', 'string', 'max:255'],
            'message'               => ['nullable', 'string', 'max:2000'],
            'inquiry_type'          => ['required', 'string', 'in:' . implode(',', [
                BusinessInquiry::TYPE_CONTACT_US,
                BusinessInquiry::TYPE_PRODUCT,
                BusinessInquiry::TYPE_SERVICE,
            ])],
            'source_id'             => ['nullable', 'integer'],
            'sources'               => ['nullable', 'array'],
            'sources.*.source_id'   => ['required_with:sources', 'integer'],
            'sources.*.source_type' => ['required_with:sources', 'string', 'in:product,service'],
            'items'                 => ['nullable', 'array'],
            'items.*.id'            => ['required_with:items', 'integer'],
            'items.*.qty'           => ['nullable', 'integer', 'min:1'],
            'items.*.price'         => ['nullable', 'numeric'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}

