<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE home_sections MODIFY COLUMN section_type VARCHAR(100) NOT NULL DEFAULT 'banner_custom'");
        } catch (\Exception $e) {
            // Fallback if alter statement fails
        }

        if (!Schema::hasColumn('home_sections', 'settings')) {
            Schema::table('home_sections', function (Blueprint $table) {
                $table->json('settings')->nullable()->after('ui_style');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('home_sections', 'settings')) {
            Schema::table('home_sections', function (Blueprint $table) {
                $table->dropColumn('settings');
            });
        }
    }
};
