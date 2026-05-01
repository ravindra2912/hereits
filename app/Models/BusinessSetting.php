<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    //

    // protected $hidden = [
    //     
    // ];

    protected $fillable = [
        'business_id',
        'is_appointment_system',
        'is_appointment_with_department',
        'is_appointment_price_required',
        'is_ecommerce_system',
        'is_product_import_export',
        'is_service_system',
        'about_us_text',
        'about_us_image',
        'subscription_expiry_date',
        'credit',
        'product_limit',
        'product_limit_expiry_date',
        'service_limit',
        'service_limit_expiry_date',
        'is_appointment_creadit_diduct_manual',
        'deduct_credit_per_customer_appointment',
        'deduct_credit_per_self_appointment',
        'is_pos_access',
        'is_verified',
    ];


    //+++++++++++++++ For api responce ================
    public function getBusinessSettingObject(): array
    {
        $data = [
            'id' => $this->id,
            'is_appointment_system' => $this->is_appointment_system == '1' ? true : false,
            'is_appointment_with_department' => $this->is_appointment_with_department == '1' ? true : false,
            'is_appointment_price_required' => $this->is_appointment_price_required == '1' ? true : false,
            'is_appointment_creadit_diduct_manual' => $this->is_appointment_creadit_diduct_manual == '1' ? true : false,
            'deduct_credit_per_customer_appointment' => $this->deduct_credit_per_customer_appointment,
            'deduct_credit_per_self_appointment' => $this->deduct_credit_per_self_appointment,
            'is_ecommerce_system' => $this->is_ecommerce_system == '1' ? true : false,
            'is_product_import_export' => $this->is_product_import_export == '1' ? true : false,
            'is_service_system' => $this->is_service_system == '1' ? true : false,
            'is_pos_access' => $this->is_pos_access == '1' ? true : false,
            'is_verified' => $this->is_verified == '1' ? true : false,
            'about_us_text' => $this->about_us_text,
            'about_us_image' => $this->about_us_image,
            'subscription_expiry_date' => $this->subscription_expiry_date,
            'credit' => (float)$this->credit,
            'product_limit' => (int)$this->product_limit,
            'product_limit_expiry_date' => $this->product_limit_expiry_date,
            'service_limit' => (int)$this->service_limit,
            'service_limit_expiry_date' => $this->service_limit_expiry_date,
            'visibility' => $this->visibility,
        ];

        return $data;
    }
}
