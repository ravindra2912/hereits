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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('device_id');
            $table->text('fcm_token');

            $table->enum('platform', [
                'web',
                'android',
                'ios',
            ]);

            $table->string('device_name')->nullable();
            $table->string('app_version')->nullable();

            $table->timestamp('last_seen_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
