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
        Schema::create('business_analytics_events', function (Blueprint $table) {
            $table->id();

            // Business
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            // Logged-in user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Visitor / Session
            $table->string('session_id', 100)->nullable()->index();
            $table->string('visitor_hash', 100)->nullable()->index();

            // Analytics Event
            $table->enum('event', [
                'view',
                'whatsapp_click',
                'call_click',
                'direction_click',
                'website_click',
                'appointment_click',
                'appointment_completed',
                'add_to_cart',
                'order_created',
                'inquiry_created',
            ])->index();

            // Page / Entity
            $table->enum('page_type', [
                'business',
                'product',
                'service',
                'expert',
            ])->nullable()->index();

            $table->unsignedBigInteger('page_id')
                ->nullable()
                ->index();

            // Traffic source
            $table->string('referer', 500)->nullable();

            $table->string('utm_source', 100)
                ->nullable()
                ->index();

            $table->string('utm_medium', 100)->nullable();

            $table->string('utm_campaign', 150)->nullable();

            // Visitor information
            $table->string('ip_address', 45)->nullable();

            $table->string('country', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();

            // Device information
            $table->enum('device', [
                'mobile',
                'tablet',
                'desktop',
                'unknown',
            ])->default('unknown');

            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();

            // Additional information
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Performance indexes
            $table->index([
                'business_id',
                'event',
                'created_at',
            ]);

            $table->index([
                'business_id',
                'page_type',
                'page_id',
            ]);

            $table->index([
                'business_id',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_analytics_events');
    }
};
