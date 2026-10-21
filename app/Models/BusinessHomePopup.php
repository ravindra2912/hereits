<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BusinessHomePopup extends Model
{
    protected $table = 'business_home_popups';

    protected $fillable = [
        'business_id',
        'title',
        'popup_type',
        'media_type',
        'form_input_fields',
        'image_url',
        'youtube_url',
        'description',
        'redirect_url',
        'trigger_delay_seconds',
        'display_frequency',
        'frequency_custom_days',
        'dismiss_behavior',
        'repeat_interval_seconds',
        'status',
        'is_web_hook_for_google_sheet',
        'google_sheet_webhook_url',
    ];

    protected $casts = [
        'form_input_fields' => 'array',
        'trigger_delay_seconds' => 'integer',
        'frequency_custom_days' => 'integer',
        'repeat_interval_seconds' => 'integer',
        'is_web_hook_for_google_sheet' => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function ($popup) {
            Cache::forget("business_{$popup->business_id}_home_popups");
        });

        static::deleted(function ($popup) {
            Cache::forget("business_{$popup->business_id}_home_popups");
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
