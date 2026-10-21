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
            if (!Schema::hasColumn('site_settings', 'draw_credit_per_scratch')) {
                $table->integer('draw_credit_per_scratch')->default(1)->after('charge_place_order_on_pos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'draw_credit_per_scratch')) {
                $table->dropColumn('draw_credit_per_scratch');
            }
        });
    }
};
