<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('credit_histories')) {
            DB::statement("ALTER TABLE `credit_histories` MODIFY COLUMN `type` ENUM('order', 'appointment', 'quotation', 'lucky_draw', 'recharge', 'manual', 'registration', 'other') NOT NULL DEFAULT 'other'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('credit_histories')) {
            DB::statement("ALTER TABLE `credit_histories` MODIFY COLUMN `type` ENUM('order', 'appointment', 'quotation', 'lucky_draw', 'recharge', 'manual', 'other') NOT NULL DEFAULT 'other'");
        }
    }
};
