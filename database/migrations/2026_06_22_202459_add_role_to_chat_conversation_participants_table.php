<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_conversation_participants', function (Blueprint $table) {
            $table->enum('role', ['admin', 'member'])->default('member')->after('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_conversation_participants', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
