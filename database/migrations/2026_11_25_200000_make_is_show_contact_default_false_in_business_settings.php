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
        if (Schema::hasTable('business_settings') && Schema::hasColumn('business_settings', 'is_show_contact')) {
            Schema::table('business_settings', function (Blueprint $table) {
                $table->boolean('is_show_contact')->default(false)->change();
            });

            DB::table('business_settings')->update(['is_show_contact' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('business_settings') && Schema::hasColumn('business_settings', 'is_show_contact')) {
            Schema::table('business_settings', function (Blueprint $table) {
                $table->boolean('is_show_contact')->default(true)->change();
            });
        }
    }
};

