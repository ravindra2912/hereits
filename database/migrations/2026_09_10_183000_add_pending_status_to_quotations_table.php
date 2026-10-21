<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('quotations')) {
            DB::statement("ALTER TABLE `quotations` MODIFY COLUMN `status` ENUM('pending', 'inprogress', 'ordered', 'cancel', 'expired') NOT NULL DEFAULT 'inprogress'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quotations')) {
            DB::statement("ALTER TABLE `quotations` MODIFY COLUMN `status` ENUM('inprogress', 'ordered', 'cancel', 'expired') NOT NULL DEFAULT 'inprogress'");
        }
    }
};
