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
            if (!Schema::hasColumn('business_settings', 'is_quotation_charge_manual')) {
                $table->boolean('is_quotation_charge_manual')->default(false)->after('is_lucky_draw');
            }
            if (!Schema::hasColumn('business_settings', 'charge_per_quotation')) {
                $table->double('charge_per_quotation', 10, 2)->default(0.10)->after('is_quotation_charge_manual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'charge_per_quotation')) {
                $table->dropColumn('charge_per_quotation');
            }
            if (Schema::hasColumn('business_settings', 'is_quotation_charge_manual')) {
                $table->dropColumn('is_quotation_charge_manual');
            }
        });
    }
};
