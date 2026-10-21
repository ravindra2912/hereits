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
        Schema::create('experts', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->integer('timing_per_appointment')->default(0);
            $table->integer('number_of_bookings_per_day')->default(0);
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('slug', 50)->unique();
            $table->boolean('is_default')->default(false);
            $table->float('rating', 2, 1)->default(0);
            $table->boolean('is_appointment_book_with_time_slot')->default(false)->comment('TRUE = book appointment with time slot, FALSE = using queue system');
            $table->boolean('is_need_booking_confirmation')->default(false)->comment('TRUE = need to confirm booking from business, FALSE = all booking are confirmed');
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('appointment_departments')->cascadeOnDelete();
            $table->index(['business_id', 'user_id']);
            $table->index(['business_id', 'status'], 'idx_experts_biz_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
