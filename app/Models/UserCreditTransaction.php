<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'transaction_id',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'credit' => 'decimal:2',
    ];

    // ==================== Constants ====================

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT  = 'debit';

    const REF_BUSINESS_SUBSCRIPTION = 'business_subscription';
    const REF_PAYOUT                = 'payout';
    const REF_ADMIN_ADJUSTMENT      = 'admin_adjustment';

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

    /**
     * Scope: filter by reference type.
     */
    public function scopeOfReferenceType($query, string $referenceType)
    {
        return $query->where('reference_type', $referenceType);
    }

    // ==================== Relationships ================

    /**
     * The user who owns this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The business associated with the transaction (if reference_type is business_subscription).
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'reference_id');
    }
}
