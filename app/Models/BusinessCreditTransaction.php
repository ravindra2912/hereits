<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCreditTransaction extends Model
{
    protected $fillable = [
        'business_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ==================== Constants ====================

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT  = 'debit';

    const REF_PURCHASE         = 'purchase';
    const REF_APPOINTMENT      = 'appointment';
    const REF_ORDER            = 'order';
    const REF_CHAT             = 'chat';
    const REF_POS              = 'pos';
    const REF_QUOTATION        = 'quotation';
    const REF_ADMIN_ADJUSTMENT = 'admin_adjustment';
    const REF_FREE_CREDIT      = 'free_credit';

    // ==================== Scopes =======================

    /**
     * Scope: only credit transactions.
     */
    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    /**
     * Scope: only debit transactions.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    // ==================== Relationships ================

    /**
     * The business associated with the transaction.
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
