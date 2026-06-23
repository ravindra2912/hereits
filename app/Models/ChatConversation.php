<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = [
        'conversation_key',
        'conversation_type',
        'title',
        'image',
        'created_by_type',
        'created_by_id',
        'last_message_id',
        'last_message_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function participants()
    {
        return $this->hasMany(ChatConversationParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_message_id');
    }

    public function creator()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
