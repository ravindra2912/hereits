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
        // 1. purchases table: plan_id noActionOnDelete
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
                $table->foreign('plan_id')->references('id')->on('plans')->noActionOnDelete();
            });
        }

        // 2. appointment_bookings table: user_id, review_id, department_id, expert_id noActionOnDelete
        if (Schema::hasTable('appointment_bookings')) {
            Schema::table('appointment_bookings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['review_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['expert_id']);

                $table->foreign('user_id')->references('id')->on('users')->noActionOnDelete();
                $table->foreign('review_id')->references('id')->on('review_and_ratings')->noActionOnDelete();
                $table->foreign('department_id')->references('id')->on('appointment_departments')->noActionOnDelete();
                $table->foreign('expert_id')->references('id')->on('experts')->noActionOnDelete();
            });
        }

        // 3. order_histories table: changed_by noActionOnDelete
        if (Schema::hasTable('order_histories')) {
            Schema::table('order_histories', function (Blueprint $table) {
                $table->dropForeign(['changed_by']);
                $table->foreign('changed_by')->references('id')->on('users')->noActionOnDelete();
            });
        }

        // 4. user_activity_histories table: user_id noActionOnDelete
        if (Schema::hasTable('user_activity_histories')) {
            Schema::table('user_activity_histories', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreign('user_id')->references('id')->on('users')->noActionOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_activity_histories')) {
            Schema::table('user_activity_histories', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('order_histories')) {
            Schema::table('order_histories', function (Blueprint $table) {
                $table->dropForeign(['changed_by']);
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('appointment_bookings')) {
            Schema::table('appointment_bookings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['review_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['expert_id']);

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('review_id')->references('id')->on('review_and_ratings')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('appointment_departments')->cascadeOnDelete();
                $table->foreign('expert_id')->references('id')->on('experts')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
                $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            });
        }
    }
};
