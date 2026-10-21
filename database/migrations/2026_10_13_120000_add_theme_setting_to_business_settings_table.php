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
        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'theme_setting')) {
                $table->json('theme_setting')->nullable()->after('accept_cash_on_delivery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'theme_setting')) {
                $table->dropColumn('theme_setting');
            }
        });
    }
};
