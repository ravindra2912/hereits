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
        DB::statement("ALTER TABLE business_leagle_pages MODIFY COLUMN type ENUM('privacy_policy', 'term_and_condition', 'refuncd_policy', 'quotation_policy') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE business_leagle_pages MODIFY COLUMN type ENUM('privacy_policy', 'term_and_condition', 'refuncd_policy') NOT NULL");
    }
};
