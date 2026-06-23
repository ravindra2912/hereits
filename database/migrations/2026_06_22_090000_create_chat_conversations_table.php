<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_key', 64)->unique();
            $table->enum('conversation_type', ['direct', 'group'])->index();
            $table->string('title')->nullable();
            $table->string('created_by_type', 50);
            $table->unsignedBigInteger('created_by_id');
            $table->unsignedBigInteger('last_message_id')->nullable()->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['created_by_type', 'created_by_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
