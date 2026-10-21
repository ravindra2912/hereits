<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessAnalyticsEvent extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * Only created_at is present in the table schema.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'user_id',
        'session_id',
        'visitor_hash',
        'event',
        'page_type',
        'page_id',
        'referer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip_address',
        'country',
        'state',
        'city',
        'device',
        'browser',
        'os',
        'metadata',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the business associated with the analytics event.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user associated with the analytics event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
