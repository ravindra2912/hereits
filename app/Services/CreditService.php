<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCreditTransaction;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Get current available credits for a business.
     */
    public function getAvailableCredits(int $businessId): float
    {
        $setting = BusinessSetting::where('business_id', $businessId)->first(['credit']);
        return (float) ($setting->credit ?? 0);
    }

    /**
     * Calculate credit deduction amount for placing an order.
     */
    public function getOrderCreditDeductionAmount(int $businessId, string $orderType = 'self'): float
    {
        $settings = BusinessSetting::where('business_id', $businessId)
            ->first(['id', 'is_order_creadit_diduct_manual', 'deduct_credit_per_customer_order', 'deduct_credit_per_self_order']);

        if ($settings && $settings->is_order_creadit_diduct_manual) {
            return (float) ($orderType === 'customer'
                ? $settings->deduct_credit_per_customer_order
                : $settings->deduct_credit_per_self_order);
        }

        $business = Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = BusinessCategory::select('id', 'deduct_credit_per_customer_order', 'deduct_credit_per_self_order')
                ->find($business->business_category_id);
            if ($category) {
                return (float) ($orderType === 'customer'
                    ? ($category->deduct_credit_per_customer_order ?? 1)
                    : ($category->deduct_credit_per_self_order ?? 1));
            }
        }

        return 1.0;
    }

    /**
     * Calculate credit deduction amount for unlocking chat.
     */
    public function getChatCreditDeductionAmount(int $businessId): float
    {
        $settings = BusinessSetting::where('business_id', $businessId)
            ->first(['id', 'is_chat_creadit_diduct_manual', 'deduct_credit_per_chat']);

        if ($settings && $settings->is_chat_creadit_diduct_manual) {
            return (float) ($settings->deduct_credit_per_chat ?? 1.0);
        }

        $business = Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = BusinessCategory::select('id', 'deduct_credit_per_chat')
                ->find($business->business_category_id);
            if ($category) {
                return (float) ($category->deduct_credit_per_chat ?? 1.0);
            }
        }

        return 1.0;
    }

    /**
     * Calculate credit deduction amount for quotation creation.
     */
    public function getQuotationCreditDeductionAmount(int $businessId): float
    {
        $settings = BusinessSetting::where('business_id', $businessId)
            ->first(['id', 'is_quotation_creadit_diduct_manual', 'deduct_credit_per_quotation']);

        if ($settings && $settings->is_quotation_creadit_diduct_manual) {
            return (float) ($settings->deduct_credit_per_quotation ?? 1.0);
        }

        $business = Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = BusinessCategory::select('id', 'deduct_credit_per_quotation')
                ->find($business->business_category_id);
            if ($category) {
                return (float) ($category->deduct_credit_per_quotation ?? 1.0);
            }
        }

        return 1.0;
    }

    /**
     * Calculate credit deduction amount for appointment booking.
     */
    public function getAppointmentCreditDeductionAmount(int $businessId, string $appointmentType = 'customer'): float
    {
        $settings = BusinessSetting::where('business_id', $businessId)
            ->first(['id', 'is_appointment_creadit_diduct_manual', 'deduct_credit_per_customer_appointment', 'deduct_credit_per_self_appointment']);

        if ($settings && $settings->is_appointment_creadit_diduct_manual) {
            return (float) ($appointmentType === 'customer'
                ? ($settings->deduct_credit_per_customer_appointment ?? 1)
                : ($settings->deduct_credit_per_self_appointment ?? 1));
        }

        $business = Business::select('id', 'business_category_id')->find($businessId);
        if ($business && $business->business_category_id) {
            $category = BusinessCategory::select('id', 'deduct_credit_per_customer_appointment', 'deduct_credit_per_self_appointment')
                ->find($business->business_category_id);
            if ($category) {
                return (float) ($appointmentType === 'customer'
                    ? ($category->deduct_credit_per_customer_appointment ?? 1)
                    : ($category->deduct_credit_per_self_appointment ?? 1));
            }
        }

        return 1.0;
    }

    /**
     * Add credit to business account.
     */
    public function addCredit(int $businessId, float $amount, string $referenceType = 'purchase', $referenceId = null, ?string $description = null): bool
    {
        if ($amount <= 0 || !$businessId) {
            return false;
        }

        try {
            DB::beginTransaction();

            $settings = BusinessSetting::firstOrCreate(['business_id' => $businessId]);
            $settings->increment('credit', $amount);

            $this->recordTransaction($businessId, BusinessCreditTransaction::TYPE_CREDIT, $amount, $referenceType, $referenceId, $description);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CreditService addCredit failed for business #{$businessId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add purchased credit to business account.
     */
    public function addPurchaseCredit(int $businessId, float $quantity, $purchaseId = null): bool
    {
        return $this->addCredit(
            $businessId,
            $quantity,
            BusinessCreditTransaction::REF_PURCHASE,
            $purchaseId,
            "Credit Purchase ({$quantity} Credits)"
        );
    }

    /**
     * Add free credit to business account.
     */
    public function addFreeCredit(int $businessId, float $amount, ?string $description = 'Initial Free Credit'): bool
    {
        return $this->addCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_FREE_CREDIT,
            null,
            $description
        );
    }

    /**
     * General credit deduction logic.
     */
    public function deductCredit(int $businessId, float $amount, string $referenceType = 'order', $referenceId = null, ?string $description = null): bool
    {
        if ($amount <= 0 || !$businessId) {
            return true;
        }

        try {
            DB::beginTransaction();

            $settings = BusinessSetting::where('business_id', $businessId)->first();
            if (!$settings) {
                DB::rollBack();
                return false;
            }

            $settings->decrement('credit', $amount);

            $this->recordTransaction($businessId, BusinessCreditTransaction::TYPE_DEBIT, $amount, $referenceType, $referenceId, $description);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CreditService deductCredit failed for business #{$businessId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deduct credit for an order.
     */
    public function deductOrderCredit(int $businessId, string $orderType = 'self', $referenceId = null, ?string $description = null): bool
    {
        $amount = $this->getOrderCreditDeductionAmount($businessId, $orderType);
        return $this->deductCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_ORDER,
            $referenceId,
            $description ?: 'Order Credit Deduction'
        );
    }

    /**
     * Deduct credit for unlocking a chat session.
     */
    public function deductChatCredit(int $businessId, $referenceId = null, ?string $description = null): bool
    {
        $amount = $this->getChatCreditDeductionAmount($businessId);
        return $this->deductCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_CHAT,
            $referenceId,
            $description ?: 'Chat Unlock Credit Deduction'
        );
    }

    /**
     * Deduct credit for an appointment booking.
     */
    public function deductAppointmentCredit(int $businessId, string $appointmentType = 'customer', $referenceId = null, ?string $description = null): bool
    {
        $amount = $this->getAppointmentCreditDeductionAmount($businessId, $appointmentType);
        return $this->deductCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_APPOINTMENT,
            $referenceId,
            $description ?: 'Appointment Credit Deduction'
        );
    }

    /**
     * Deduct credit for a quotation creation.
     */
    public function deductQuotationCredit(int $businessId, $referenceId = null, ?string $description = null): bool
    {
        $amount = $this->getQuotationCreditDeductionAmount($businessId);
        $defaultDesc = "Deducted " . number_format($amount, 2) . " Credit(s) for Quotation" . ($referenceId ? " #{$referenceId}" : "");
        return $this->deductCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_QUOTATION,
            $referenceId,
            $description ?: $defaultDesc
        );
    }

    /**
     * Deduct credit for a POS sale.
     */
    public function deductPosCredit(int $businessId, $referenceId = null, ?string $description = null): bool
    {
        $amount = $this->getOrderCreditDeductionAmount($businessId, 'self');
        return $this->deductCredit(
            $businessId,
            $amount,
            BusinessCreditTransaction::REF_POS,
            $referenceId,
            $description ?: 'POS Sale Credit Deduction'
        );
    }

    /**
     * Record transaction log into business_credit_transactions table.
     */
    public function recordTransaction(int $businessId, string $type, float $amount, string $referenceType = 'purchase', $referenceId = null, ?string $description = null): ?BusinessCreditTransaction
    {
        if ($amount <= 0 || !$businessId) {
            return null;
        }

        return BusinessCreditTransaction::create([
            'business_id'    => $businessId,
            'type'           => $type,
            'amount'         => $amount,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'description'    => $description,
        ]);
    }
}
