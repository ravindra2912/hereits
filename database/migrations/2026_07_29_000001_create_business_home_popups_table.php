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
        Schema::create('business_home_popups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('title', 255);
            $table->enum('popup_type', ['image', 'content', 'image_with_content', 'input_form']);
            $table->json('form_input_fields')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->string('redirect_url', 2048)->nullable();
            $table->integer('trigger_delay_seconds')->default(0);
            $table->enum('display_frequency', ['daily', 'weekly', 'monthly', 'yearly', 'custom_days'])->default('daily');
            $table->integer('frequency_custom_days')->nullable();
            $table->enum('dismiss_behavior', ['repeat', 'none'])->default('none');
            $table->integer('repeat_interval_seconds')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_home_popups');
    }
};
