<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCampaignLead extends Model
{
    protected $table = 'business_campaign_leads';

    protected $fillable = [
        'business_id',
        'popup_id',
        'assigned_by',
        'assigned_to',
        'contact',
        'email',
        'details',
        'ip_address',
        'status',
        'is_webhook_sent',
        'webhook_response',
        'scheduled_at',
    ];

    protected $casts = [
        'details' => 'array',
        'is_webhook_sent' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    public function popup()
    {
        return $this->belongsTo(BusinessHomePopup::class, 'popup_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
