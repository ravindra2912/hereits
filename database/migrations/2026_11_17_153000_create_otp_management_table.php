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
        Schema::create('otp_management', function (Blueprint $table) {
            $table->id();
            $table->string('contact', 20)->index();
            $table->string('otp', 10);
            $table->enum('source', ['whatsapp', 'sms', 'email'])->default('whatsapp');
            $table->unsignedInteger('valid')->default(10)->comment('Validity in minutes');
            $table->enum('status', ['pending', 'verified', 'expired', 'used', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('token', 100)->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['contact', 'source', 'status']);
            $table->index(['contact', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_management');
    }
};

