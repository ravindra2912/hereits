<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyDrawPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'lucky_draw_id',
        'name',
        'image_path',
        'win_percentage',
        'total_won',
    ];

    protected $casts = [
        'win_percentage' => 'float',
        'total_won' => 'integer',
    ];

    public function luckyDraw()
    {
        return $this->belongsTo(LuckyDraw::class);
    }

    public function entries()
    {
        return $this->hasMany(LuckyDrawEntry::class, 'won_prize_id');
    }

    public function getImageUrlAttribute()
    {
        return getImage($this->image_path);
    }
}
