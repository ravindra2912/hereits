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
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'free_credit')) {
                $table->integer('free_credit')->default(30)->after('payment_gateway')->comment('Free credit given to newly registered business');
            }
            if (Schema::hasColumn('site_settings', 'free_trial_days')) {
                $table->dropColumn('free_trial_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'free_credit')) {
                $table->dropColumn('free_credit');
            }
            if (!Schema::hasColumn('site_settings', 'free_trial_days')) {
                $table->integer('free_trial_days')->default(7)->after('payment_gateway')->comment('Free trial period in days');
            }
        });
    }
};
