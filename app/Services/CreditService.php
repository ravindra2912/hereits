<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\CreditHistory;
use App\Models\SiteSetting;
use Exception;

class CreditService
{
    /**
     * Get available credit balance for a business.
     *
     * @param int $businessId
     * @return float
     */
    public function getAvailableCredit(int $businessId): float
    {
        $setting = BusinessSetting::where('business_id', $businessId)->first(['credit']);
        return $setting ? (float)$setting->credit : 0.0;
    }

    /**
     * Get charge required for creating a quotation.
     *
     * @param int $businessId
     * @return float
     */
    public function getQuotationCharge(int $businessId): float
    {
        $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_quotation_charge_manual', 'charge_per_quotation']);
        if ($businessSetting && $businessSetting->is_quotation_charge_manual) {
            return (float)($businessSetting->charge_per_quotation ?? 0.10);
        }
        $siteSetting = getSiteSetting();
        return (float)($siteSetting->charge_per_quotation ?? 0.10);
    }

    /**
     * Check if a business has sufficient credits for quotation creation.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkQuotationCredit(int $businessId): float
    {
        $charge = $this->getQuotationCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'generate quotation');
        return $charge;
    }

    /**
     * Deduct quotation charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductQuotationCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getQuotationCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'quotation', 'Quotation credit deducted', $referenceId);
        }
    }

    /**
     * Get charge required for placing a POS order.
     *
     * @param int|null $businessId
     * @return float
     */
    public function getPosOrderCharge(?int $businessId = null): float
    {
        if (!$businessId && auth()->check()) {
            $user = auth()->user();
            $businessId = $user->business_id ?? ($user->business->id ?? null);
        }

        if ($businessId) {
            $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_order_charge_manual', 'charge_place_order_on_pos']);
            if ($businessSetting && $businessSetting->is_order_charge_manual) {
                return (float)($businessSetting->charge_place_order_on_pos ?? 0.25);
            }
        }
        $siteSetting = getSiteSetting();
        return (float)($siteSetting->charge_place_order_on_pos ?? 0.25);
    }

    /**
     * Check if a business has sufficient credits for placing a POS order.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkPosOrderCredit(int $businessId): float
    {
        $charge = $this->getPosOrderCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'place order');
        return $charge;
    }

    /**
     * Deduct POS order charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductPosOrderCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getPosOrderCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'order', 'POS order credit deducted', $referenceId);
        }
    }

    /**
     * Get charge required for placing a website order.
     *
     * @param int|null $businessId
     * @return float
     */
    public function getWebOrderCharge(?int $businessId = null): float
    {
        if (!$businessId && auth()->check()) {
            $user = auth()->user();
            $businessId = $user->business_id ?? ($user->business->id ?? null);
        }

        if ($businessId) {
            $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_order_charge_manual', 'charge_place_order_on_website']);
            if ($businessSetting && $businessSetting->is_order_charge_manual) {
                return (float)($businessSetting->charge_place_order_on_website ?? 0.0);
            }
        }
        $siteSetting = getSiteSetting();
        return (float)($siteSetting->charge_place_order_on_website ?? 0.0);
    }

    /**
     * Check if a business has sufficient credits for placing a website order.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkWebOrderCredit(int $businessId): float
    {
        $charge = $this->getWebOrderCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'place website order');
        return $charge;
    }

    /**
     * Deduct website order charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductWebOrderCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getWebOrderCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'order', 'Website order credit deducted', $referenceId);
        }
    }

    /**
     * Get charge for customer appointment booking.
     *
     * @param int $businessId
     * @return float
     */
    public function getCustomerAppointmentCharge(int $businessId): float
    {
        $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_appointment_creadit_diduct_manual', 'deduct_credit_per_customer_appointment']);
        if ($businessSetting && $businessSetting->is_appointment_creadit_diduct_manual) {
            return (float)($businessSetting->deduct_credit_per_customer_appointment ?? 1);
        }

        $business = \App\Models\Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = \App\Models\BusinessCategory::select('id', 'deduct_credit_per_customer_appointment')->find($business->business_category_id);
            return (float)($category->deduct_credit_per_customer_appointment ?? 1);
        }

        return 1.0;
    }

    /**
     * Check if a business has sufficient credits for customer appointment booking.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkCustomerAppointmentCredit(int $businessId): float
    {
        $charge = $this->getCustomerAppointmentCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'book customer appointment');
        return $charge;
    }

    /**
     * Deduct customer appointment charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductCustomerAppointmentCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getCustomerAppointmentCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'appointment', 'Customer appointment credit deducted', $referenceId);
        }
    }

    /**
     * Get charge for self appointment booking (admin/expert).
     *
     * @param int $businessId
     * @return float
     */
    public function getSelfAppointmentCharge(int $businessId): float
    {
        $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_appointment_creadit_diduct_manual', 'deduct_credit_per_self_appointment']);
        if ($businessSetting && $businessSetting->is_appointment_creadit_diduct_manual) {
            return (float)($businessSetting->deduct_credit_per_self_appointment ?? 1);
        }

        $business = \App\Models\Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = \App\Models\BusinessCategory::select('id', 'deduct_credit_per_self_appointment')->find($business->business_category_id);
            return (float)($category->deduct_credit_per_self_appointment ?? 1);
        }

        return 1.0;
    }

    /**
     * Check if a business has sufficient credits for self appointment booking.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkSelfAppointmentCredit(int $businessId): float
    {
        $charge = $this->getSelfAppointmentCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'book self appointment');
        return $charge;
    }

    /**
     * Deduct self appointment charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductSelfAppointmentCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getSelfAppointmentCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'appointment', 'Self appointment credit deducted', $referenceId);
        }
    }

    /**
     * Get charge required for generating a lucky draw scratch card.
     *
     * @param int|null $businessId
     * @return float
     */
    public function getLuckyDrawCharge(?int $businessId = null): float
    {
        if (!$businessId && auth()->check()) {
            $user = auth()->user();
            $businessId = $user->business_id ?? ($user->business->id ?? null);
        }

        if ($businessId) {
            $businessSetting = BusinessSetting::where('business_id', $businessId)->first(['is_draw_charge_manual', 'draw_credit_per_scratch']);
            if ($businessSetting && $businessSetting->is_draw_charge_manual) {
                return (float)($businessSetting->draw_credit_per_scratch ?? 1.00);
            }
        }

        $siteSetting = getSiteSetting();
        return (float)($siteSetting->draw_credit_per_scratch ?? 1.00);
    }

    /**
     * Check if a business has sufficient credits for generating a lucky draw scratch card.
     *
     * @param int $businessId
     * @return float Required charge amount
     * @throws Exception
     */
    public function checkLuckyDrawCredit(int $businessId): float
    {
        $charge = $this->getLuckyDrawCharge($businessId);
        $this->checkSufficientCredit($businessId, $charge, 'generate lucky draw scratch card');
        return $charge;
    }

    /**
     * Deduct lucky draw scratch card charge from business credit balance.
     *
     * @param int $businessId
     * @param mixed $referenceId
     * @return void
     */
    public function deductLuckyDrawCharge(int $businessId, $referenceId = null): void
    {
        $charge = $this->getLuckyDrawCharge($businessId);
        if ($charge > 0) {
            $this->deductCredit($businessId, $charge, 'lucky_draw', 'Lucky draw scratch card credit deducted', $referenceId);
        }
    }

    /**
     * Generic method to check if business has sufficient credit for a given charge.
     *
     * @param int $businessId
     * @param float $requiredCharge
     * @param string $actionName
     * @return void
     * @throws Exception
     */
    public function checkSufficientCredit(int $businessId, float $requiredCharge, string $actionName = 'perform this action'): void
    {
        if ($requiredCharge <= 0) {
            return;
        }

        $availableCredit = $this->getAvailableCredit($businessId);

        if ($availableCredit < $requiredCharge) {
            $reqFormatted = number_format($requiredCharge, 2);
            $availFormatted = number_format($availableCredit, 2);
            throw new Exception("Insufficient credit balance to {$actionName}. Required: {$reqFormatted} credits, Available: {$availFormatted} credits. Please recharge your Credits.");
        }
    }

    /**
     * Add credit to a business wallet and log history.
     *
     * @param int $businessId
     * @param float $amount
     * @param string $type
     * @param string|null $description
     * @param mixed|null $referenceId
     * @return void
     */
    public function addCredit(int $businessId, float $amount, string $type = 'recharge', ?string $description = null, $referenceId = null): void
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return;
        }

        $setting = BusinessSetting::where('business_id', $businessId)->first();
        if (!$setting) {
            $setting = BusinessSetting::create(['business_id' => $businessId, 'credit' => 0]);
        }

        $setting->increment('credit', $amount);
        $setting->refresh();

        CreditHistory::create([
            'business_id'      => $businessId,
            'transaction_type' => 'credit',
            'type'             => $type,
            'amount'           => $amount,
            'balance_after'    => (float)$setting->credit,
            'description'      => $description ?? 'Credit recharged',
            'reference_id'     => $referenceId ? (string)$referenceId : null,
        ]);

        if (function_exists('getBusinessSettings')) {
            getBusinessSettings($businessId, true);
        }
    }

    /**
     * Generic method to deduct credits from a business wallet and log history.
     *
     * @param int $businessId
     * @param float $amount
     * @param string $type
     * @param string|null $description
     * @param mixed|null $referenceId
     * @return void
     */
    public function deductCredit(int $businessId, float $amount, string $type = 'other', ?string $description = null, $referenceId = null): void
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return;
        }

        $setting = BusinessSetting::where('business_id', $businessId)->first();
        if ($setting) {
            // Prevent negative balance underflow
            $newBalance = max(0.0, (float)$setting->credit - $amount);
            $setting->update(['credit' => $newBalance]);

            CreditHistory::create([
                'business_id'      => $businessId,
                'transaction_type' => 'debit',
                'type'             => $type,
                'amount'           => $amount,
                'balance_after'    => $newBalance,
                'description'      => $description ?? 'Credit deducted',
                'reference_id'     => $referenceId ? (string)$referenceId : null,
            ]);

            if (function_exists('getBusinessSettings')) {
                getBusinessSettings($businessId, true);
            }
        }
    }
}
