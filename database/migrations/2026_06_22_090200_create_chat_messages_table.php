<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('sender_type', 50);
            $table->unsignedBigInteger('sender_id');
            $table->enum('message_type', ['text', 'image', 'quotation','inquiry', 'order', 'system'])->default('text')->index();
            $table->string('action_type', 30)->nullable()->index();
            $table->text('body')->nullable(); 
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('reply_to_message_id')->nullable()->index();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_type', 'sender_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
