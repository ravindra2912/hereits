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
        Schema::create('business_campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->foreignId('popup_id')->nullable()->constrained('business_home_popups')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('contact', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', [
                'new',
                'contacted',
                'scheduled',
                'in_progress',
                'converted',
                'closed_lost',
                'duplicate',
                'spam'
            ])->default('new');
            $table->dateTime('scheduled_at')->nullable();
            $table->boolean('is_webhook_sent')->default(false);
            $table->timestamps();

            $table->index(['popup_id', 'status']);
            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_campaign_leads');
    }
};
