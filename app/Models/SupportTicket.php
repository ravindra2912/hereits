<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'subject',
        'category',
        'description',
        'status',
        'priority',
        'creator_id',
        'creator_type',
        'email',
        'contact',
    ];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                do {
                    $number = 'TIC-' . date('Ymd') . '-' . rand(1000, 9999);
                } while (static::where('ticket_number', $number)->exists());
                $ticket->ticket_number = $number;
            }
        });
    }

    /**
     * Get the owning creator model (User or Admin).
     */
    public function creator()
    {
        return $this->morphTo();
    }
}
