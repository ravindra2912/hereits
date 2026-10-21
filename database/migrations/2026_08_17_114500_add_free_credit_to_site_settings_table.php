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
        if (Schema::hasTable('site_settings') && !Schema::hasColumn('site_settings', 'free_credit')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->integer('free_credit')->default(30)->comment('Free initial credit for business signup')->after('free_service_limit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'free_credit')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('free_credit');
            });
        }
    }
};
