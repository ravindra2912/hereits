<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('participant_type', 50);
            $table->unsignedBigInteger('participant_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable()->index();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('last_read_message_id')->nullable()->index();
            $table->timestamp('last_read_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'chat_participants_unique');
            $table->index(['participant_type', 'participant_id'], 'chat_participants_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversation_participants');
    }
};
