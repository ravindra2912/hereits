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
        Schema::create('business_inquiry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('business_inquiries')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', [
                'created',
                'accepted',
                'rejected',
                'not_fulfilled',
                'assigned',
                'reassigned',
                'system_status_changed',
                'business_status_changed',
                'follow_up_updated',
                'note_added'
            ]);
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('inquiry_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_inquiry_logs');
    }
};

