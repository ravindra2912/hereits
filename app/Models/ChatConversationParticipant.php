<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversationParticipant extends Model
{
    protected $fillable = [
        'conversation_id',
        'participant_type',
        'participant_id',
        'role',
        'joined_at',
        'left_at',
        'is_muted',
        'is_archived',
        'last_read_message_id',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'last_read_at' => 'datetime',
            'is_muted' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function participant()
    {
        return $this->morphTo();
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_read_message_id');
    }
}
