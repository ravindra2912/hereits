<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessAnalyticsEvent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business_analytics_events';

    /**
     * Disable standard updated_at timestamp handling since only created_at exists.
     */
    const UPDATED_AT = null;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'page_id'    => 'integer',
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ==================== Event Constants ====================

    const EVENT_VIEW                  = 'view';
    const EVENT_WHATSAPP_CLICK        = 'whatsapp_click';
    const EVENT_CALL_CLICK            = 'call_click';
    const EVENT_DIRECTION_CLICK       = 'direction_click';
    const EVENT_WEBSITE_CLICK         = 'website_click';
    const EVENT_APPOINTMENT_CLICK     = 'appointment_click';
    const EVENT_APPOINTMENT_COMPLETED = 'appointment_completed';
    const EVENT_ADD_TO_CART           = 'add_to_cart';
    const EVENT_ORDER_CREATED         = 'order_created';
    const EVENT_INQUIRY_CREATED       = 'inquiry_created';

    // ==================== Page Type Constants =================

    const PAGE_TYPE_BUSINESS = 'business';
    const PAGE_TYPE_PRODUCT  = 'product';
    const PAGE_TYPE_SERVICE  = 'service';
    const PAGE_TYPE_EXPERT   = 'expert';

    // ==================== Device Constants ====================

    const DEVICE_MOBILE  = 'mobile';
    const DEVICE_TABLET  = 'tablet';
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_UNKNOWN = 'unknown';

    // ==================== Relationships =======================

    /**
     * Get the business associated with the analytics event.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the logged-in user who triggered the event, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Query Scopes ========================

    /**
     * Scope a query to filter by business ID.
     */
    public function scopeForBusiness($query, int|string $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope a query to filter by event type.
     */
    public function scopeOfEvent($query, string|array $event)
    {
        return is_array($event)
            ? $query->whereIn('event', $event)
            : $query->where('event', $event);
    }

    /**
     * Scope a query to filter by page type and optional page ID.
     */
    public function scopeOfPage($query, string $pageType, ?int $pageId = null)
    {
        $query->where('page_type', $pageType);

        if ($pageId !== null) {
            $query->where('page_id', $pageId);
        }

        return $query;
    }

    /**
     * Scope a query to filter between date ranges.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter for events created today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to filter for events created within the last N days.
     */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to filter by device type.
     */
    public function scopeOfDevice($query, string $device)
    {
        return $query->where('device', $device);
    }
}
