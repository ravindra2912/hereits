<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `status` ENUM('active', 'in-active', 'in-active-expiry') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE `services` MODIFY COLUMN `status` ENUM('active', 'in-active', 'in-active-expiry') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->where('status', 'in-active-expiry')->update(['status' => 'in-active']);
        DB::table('services')->where('status', 'in-active-expiry')->update(['status' => 'in-active']);

        DB::statement("ALTER TABLE `products` MODIFY COLUMN `status` ENUM('active', 'in-active') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE `services` MODIFY COLUMN `status` ENUM('active', 'in-active') NOT NULL DEFAULT 'active'");
    }
};
