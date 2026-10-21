<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    //

    protected $hidden = [
        'razorpay_key_secret',
    ];

    protected $fillable = [
        'business_id',
        'is_appointment_system',
        'is_appointment_with_department',
        'is_appointment_price_required',
        'allow_appointment_product_selection',
        'is_ecommerce_system',
        'is_offer_with_customer_group',
        'is_label',
        'is_share_products_to_business',
        'is_order_place_on_website',
        'is_product_import_export',
        'is_update_stock_and_price_using_exel',
        'is_service_system',
        'is_show_contact',
        'about_us_text',
        'about_us_image',
        'credit',
        'is_appointment_creadit_diduct_manual',
        'deduct_credit_per_customer_appointment',
        'deduct_credit_per_self_appointment',
        'is_pos_access',
        'is_lucky_draw',
        'is_quotation_charge_manual',
        'is_create_quotation_on_inquiry',
        'is_login_required',
        'charge_per_quotation',
        'is_order_charge_manual',
        'charge_place_order_on_website',
        'charge_place_order_on_pos',
        'is_draw_charge_manual',
        'draw_credit_per_scratch',
        'is_verified',
        'is_private',
        'show_delivery_charges',
        'same_state_shipping_charge_per_kg',
        'other_state_shipping_charge_per_kg',
        'accept_online_payments',
        'accept_cash_on_delivery',
        'theme_setting',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_login_required' => 'boolean',
        'theme_setting' => 'array',
    ];

    /**
     * Alias for is_privet spelling.
     */
    public function getIsPrivetAttribute(): bool
    {
        return (bool) ($this->is_private ?? false);
    }

    public function setIsPrivetAttribute($value): void
    {
        $this->attributes['is_private'] = $value;
    }

    //+++++++++++++++ For api responce ================
    public function getBusinessSettingObject(): array
    {
        $data = [
            'id' => $this->id,
            'is_appointment_system' => $this->is_appointment_system == '1' ? true : false,
            'is_appointment_with_department' => $this->is_appointment_with_department == '1' ? true : false,
            'is_appointment_price_required' => $this->is_appointment_price_required == '1' ? true : false,
            'allow_appointment_product_selection' => $this->allow_appointment_product_selection == '1' ? true : false,
            'is_appointment_creadit_diduct_manual' => $this->is_appointment_creadit_diduct_manual == '1' ? true : false,
            'deduct_credit_per_customer_appointment' => $this->deduct_credit_per_customer_appointment,
            'deduct_credit_per_self_appointment' => $this->deduct_credit_per_self_appointment,
            'is_ecommerce_system' => $this->is_ecommerce_system == '1' ? true : false,
            'is_offer_with_customer_group' => $this->is_offer_with_customer_group == '1' ? true : false,
            'is_label' => $this->is_label == '1' ? true : false,
            'is_share_products_to_business' => $this->is_share_products_to_business == '1' ? true : false,
            'is_order_place_on_website' => $this->is_order_place_on_website == '1' ? true : false,
            'is_product_import_export' => $this->is_product_import_export == '1' ? true : false,
            'is_update_stock_and_price_using_exel' => $this->is_update_stock_and_price_using_exel == '1' ? true : false,
            'is_service_system' => $this->is_service_system == '1' ? true : false,
            'is_show_contact' => $this->is_show_contact == '1' ? true : false,
            'is_pos_access' => $this->is_pos_access == '1' ? true : false,
            'is_lucky_draw' => $this->is_lucky_draw == '1' ? true : false,
            'is_quotation_charge_manual' => $this->is_quotation_charge_manual == '1' ? true : false,
            'is_create_quotation_on_inquiry' => $this->is_create_quotation_on_inquiry == '1' ? true : false,
            'is_login_required' => (bool) ($this->is_login_required ?? false),
            'charge_per_quotation' => (float)($this->charge_per_quotation ?? 0.10),
            'is_order_charge_manual' => $this->is_order_charge_manual == '1' ? true : false,
            'charge_place_order_on_website' => (float)($this->charge_place_order_on_website ?? 0.00),
            'charge_place_order_on_pos' => (float)($this->charge_place_order_on_pos ?? 0.00),
            'is_draw_charge_manual' => $this->is_draw_charge_manual == '1' ? true : false,
            'draw_credit_per_scratch' => (float)($this->draw_credit_per_scratch ?? 1.00),
            'is_verified' => $this->is_verified == '1' ? true : false,
            'is_private' => (bool)($this->is_private ?? false),
            'about_us_text' => $this->about_us_text,
            'about_us_image' => $this->about_us_image,
            'credit' => (float)$this->credit,
            'show_delivery_charges' => $this->show_delivery_charges == '1' ? true : false,
            'same_state_shipping_charge_per_kg' => (float)$this->same_state_shipping_charge_per_kg,
            'other_state_shipping_charge_per_kg' => (float)$this->other_state_shipping_charge_per_kg,
            'accept_online_payments' => $this->accept_online_payments == '1' ? true : false,
            'payment_method' => $this->paymentConfiguration?->payment_method ?? 'manual_upi',
            'online_payment_method' => $this->paymentConfiguration?->payment_method ?? 'manual_upi',
            'upi_id' => $this->paymentConfiguration?->upi_id,
            'razorpay_key_id' => $this->paymentConfiguration?->razorpay_key_id,
            'razorpay_key_secret' => $this->paymentConfiguration?->razorpay_key_secret,
            'accept_cash_on_delivery' => $this->accept_cash_on_delivery == '1' ? true : false,
            'theme_setting' => $this->theme_setting ?? config('theme.default_theme_setting', []),
        ];

        return $data;
    }

    public function paymentConfiguration()
    {
        return $this->hasOne(BusinessPaymentConfiguration::class, 'business_id', 'business_id');
    }
}
