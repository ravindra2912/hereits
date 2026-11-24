<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'assigned_to',
        'name',
        'contact',
        'email',
        'message',
        'inquiry_type',
        'system_status',
        'business_status',
        'credit_deducted',
        'credit_deducted_id',
        'accepted_at',
        'contacted_at',
        'next_follow_up_at',
        'converted_at',
        'closed_at',
    ];

    protected $casts = [
        'credit_deducted' => 'boolean',
        'accepted_at' => 'datetime',
        'contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // System Status Constants
    public const SYSTEM_STATUS_PENDING = 'pending';
    public const SYSTEM_STATUS_ACCEPTED = 'accepted';
    public const SYSTEM_STATUS_REJECTED = 'rejected';
    public const SYSTEM_STATUS_NOT_FULFILLED = 'not_fulfilled';

    // Business Status Constants
    public const BUSINESS_STATUS_NEW = 'new';
    public const BUSINESS_STATUS_CONTACTED = 'contacted';
    public const BUSINESS_STATUS_FOLLOW_UP = 'follow_up';
    public const BUSINESS_STATUS_SCHEDULED = 'scheduled';
    public const BUSINESS_STATUS_CONVERTED = 'converted';
    public const BUSINESS_STATUS_CLOSED = 'closed';

    // Inquiry Types
    public const TYPE_CONTACT_US = 'contact_us';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creditDeducted()
    {
        return $this->belongsTo(CreditHistory::class, 'credit_deducted_id');
    }

    public function sources()
    {
        return $this->hasMany(BusinessInquirySource::class, 'inquiry_id');
    }

    public function logs()
    {
        return $this->hasMany(BusinessInquiryLog::class, 'inquiry_id')->latest();
    }

    // Helper checks
    public function isAccepted(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_ACCEPTED;
    }

    public function isPending(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_REJECTED;
    }

    public function isNotFulfilled(): bool
    {
        return $this->system_status === self::SYSTEM_STATUS_NOT_FULFILLED;
    }

    /**
     * Privacy rule: In pending (or not accepted) status, mask customer name.
     */
    public function getMaskedNameAttribute(): string
    {
        if ($this->isAccepted()) {
            return (string)$this->name;
        }

        $length = mb_strlen((string)$this->name);
        if ($length <= 2) {
            return str_repeat('*', max(1, $length));
        }
        return mb_substr($this->name, 0, 1) . str_repeat('*', max(1, $length - 2)) . mb_substr($this->name, -1);
    }

    /**
     * Privacy rule: In pending (or not accepted) status, mask customer contact.
     */
    public function getMaskedContactAttribute(): string
    {
        if ($this->isAccepted()) {
            return (string)$this->contact;
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string)$this->contact);
        $len = strlen($cleaned);
        if ($len <= 4) {
            return str_repeat('*', max(1, $len));
        }
        return str_repeat('*', $len - 4) . substr($cleaned, -4);
    }

    /**
     * Privacy rule: In pending (or not accepted) status, mask customer email.
     */
    public function getMaskedEmailAttribute(): ?string
    {
        if (!$this->email) {
            return null;
        }

        if ($this->isAccepted()) {
            return $this->email;
        }

        $parts = explode('@', $this->email);
        $namePart = $parts[0];
        $domain = $parts[1] ?? '';

        $maskedName = strlen($namePart) > 2
            ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
            : str_repeat('*', strlen($namePart));

        return $maskedName . '@' . ($domain ? '***.' . substr(strrchr($domain, '.'), 1) : '***');
    }
}

