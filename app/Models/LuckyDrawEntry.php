<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyDrawEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'lucky_draw_id',
        'created_by_id',
        'customer_name',
        'customer_contact',
        'for',
        'source_id',
        'token',
        'credits_deducted',
        'won_prize_id',
        'scratched_card_index',
        'status',
        'scratched_at',
    ];

    protected $casts = [
        'credits_deducted' => 'integer',
        'scratched_at' => 'datetime',
    ];

    public function luckyDraw()
    {
        return $this->belongsTo(LuckyDraw::class);
    }

    public function wonPrize()
    {
        return $this->belongsTo(LuckyDrawPrize::class, 'won_prize_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'source_id');
    }
}
