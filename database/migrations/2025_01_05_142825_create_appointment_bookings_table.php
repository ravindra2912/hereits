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
        Schema::create('appointment_bookings', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->integer('token_number');
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('expert_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_contact')->nullable();
            $table->dateTime('slot_start_time')->nullable();
            $table->dateTime('slot_end_time')->nullable();
            $table->date('booking_date');
            $table->string('note', 250)->nullable();
            $table->string('expert_note', 250)->nullable();
            $table->double('amount', 10, 2)->default(0);
            $table->enum('payment_type', ['Cash', 'Online'])->default('Cash');
            $table->unsignedBigInteger('review_id')->nullable();
            $table->enum('appointment_for', ['self', 'other'])->default('self');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancel', 'cancel_by_user', 'auto_cancelled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('appointment_departments')->cascadeOnDelete();
            $table->foreign('expert_id')->references('id')->on('experts')->cascadeOnDelete();
            $table->foreign('review_id')->references('id')->on('review_and_ratings')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_bookings');
    }
};
