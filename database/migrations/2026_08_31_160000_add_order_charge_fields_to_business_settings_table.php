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
            if (!Schema::hasColumn('business_settings', 'is_order_charge_manual')) {
                $table->boolean('is_order_charge_manual')->default(false)->after('charge_per_quotation');
            }
            if (!Schema::hasColumn('business_settings', 'charge_place_order_on_website')) {
                $table->double('charge_place_order_on_website', 10, 2)->default(0.00)->after('is_order_charge_manual');
            }
            if (!Schema::hasColumn('business_settings', 'charge_place_order_on_pos')) {
                $table->double('charge_place_order_on_pos', 10, 2)->default(0.00)->after('charge_place_order_on_website');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'is_order_charge_manual')) {
                $table->dropColumn('is_order_charge_manual');
            }
            if (Schema::hasColumn('business_settings', 'charge_place_order_on_website')) {
                $table->dropColumn('charge_place_order_on_website');
            }
            if (Schema::hasColumn('business_settings', 'charge_place_order_on_pos')) {
                $table->dropColumn('charge_place_order_on_pos');
            }
        });
    }
};
