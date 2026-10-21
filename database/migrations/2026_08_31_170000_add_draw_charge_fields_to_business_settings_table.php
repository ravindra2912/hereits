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
            if (!Schema::hasColumn('business_settings', 'is_draw_charge_manual')) {
                $table->boolean('is_draw_charge_manual')->default(false)->after('charge_place_order_on_pos');
            }
            if (!Schema::hasColumn('business_settings', 'draw_credit_per_scratch')) {
                $table->double('draw_credit_per_scratch', 10, 2)->default(1.00)->after('is_draw_charge_manual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'is_draw_charge_manual')) {
                $table->dropColumn('is_draw_charge_manual');
            }
            if (Schema::hasColumn('business_settings', 'draw_credit_per_scratch')) {
                $table->dropColumn('draw_credit_per_scratch');
            }
        });
    }
};
