<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message_type',
        'action_type',
        'body',
        'metadata',
        'reply_to_message_id',
        'is_system',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_system' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_message_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatMessageAttachment::class, 'message_id');
    }
}
