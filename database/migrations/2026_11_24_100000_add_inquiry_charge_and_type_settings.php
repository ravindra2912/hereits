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
        // Add 'inquiry' to credit_histories enum
        if (Schema::hasTable('credit_histories')) {
            DB::statement("ALTER TABLE credit_histories MODIFY COLUMN type ENUM('order', 'appointment', 'quotation', 'lucky_draw', 'recharge', 'manual', 'registration', 'inquiry', 'other') DEFAULT 'other'");
        }

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'charge_per_inquiry')) {
                    $table->double('charge_per_inquiry', 10, 2)->default(1.00)->after('charge_per_quotation')->comment('Credits charged per inquiry acceptance');
                }
            });
        }

        if (Schema::hasTable('business_settings')) {
            Schema::table('business_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('business_settings', 'is_inquiry_charge_manual')) {
                    $table->boolean('is_inquiry_charge_manual')->default(false)->after('is_quotation_charge_manual');
                }
                if (!Schema::hasColumn('business_settings', 'charge_per_inquiry')) {
                    $table->double('charge_per_inquiry', 10, 2)->default(1.00)->after('charge_per_quotation');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('business_settings')) {
            Schema::table('business_settings', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('business_settings', 'charge_per_inquiry')) {
                    $columns[] = 'charge_per_inquiry';
                }
                if (Schema::hasColumn('business_settings', 'is_inquiry_charge_manual')) {
                    $columns[] = 'is_inquiry_charge_manual';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'charge_per_inquiry')) {
                    $table->dropColumn('charge_per_inquiry');
                }
            });
        }

        if (Schema::hasTable('credit_histories')) {
            DB::statement("ALTER TABLE credit_histories MODIFY COLUMN type ENUM('order', 'appointment', 'quotation', 'lucky_draw', 'recharge', 'manual', 'registration', 'other') DEFAULT 'other'");
        }
    }
};

