<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpManagement extends Model
{
    use HasFactory;

    protected $table = 'otp_management';

    protected $fillable = [
        'contact',
        'otp',
        'source',
        'valid',
        'status',
        'attempts',
        'token',
        'verified_at',
    ];

    protected $casts = [
        'valid' => 'integer',
        'attempts' => 'integer',
        'verified_at' => 'datetime',
    ];

    /**
     * Check whether this OTP is expired based on validity minutes.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired' || $this->status === 'used') {
            return true;
        }

        return $this->created_at->addMinutes($this->valid)->isPast();
    }

    /**
     * Check whether this OTP is active and can be verified.
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired() && $this->attempts < 5;
    }

    /**
     * Get remaining validity time in seconds.
     */
    public function getRemainingSeconds(): int
    {
        $expiresAt = $this->created_at->addMinutes($this->valid);
        return max(0, $expiresAt->diffInSeconds(now()));
    }
}

