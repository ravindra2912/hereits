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
        // 1. Remove charge_per_inquiry from site_settings (no longer managed there)
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'charge_per_inquiry')) {
                    $table->dropColumn('charge_per_inquiry');
                }
            });
        }

        // 2. Add per-inquiry-type charges to business_categories table (default 1.00)
        if (Schema::hasTable('business_categories')) {
            Schema::table('business_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('business_categories', 'charge_per_contact_us_inquiry')) {
                    $table->double('charge_per_contact_us_inquiry', 10, 2)->default(1.00)->after('deduct_credit_per_self_appointment');
                }
                if (!Schema::hasColumn('business_categories', 'charge_per_product_inquiry')) {
                    $table->double('charge_per_product_inquiry', 10, 2)->default(1.00)->after('charge_per_contact_us_inquiry');
                }
                if (!Schema::hasColumn('business_categories', 'charge_per_service_inquiry')) {
                    $table->double('charge_per_service_inquiry', 10, 2)->default(1.00)->after('charge_per_product_inquiry');
                }
            });
        }

        // 3. Update business_settings table with per-inquiry-type charges
        if (Schema::hasTable('business_settings')) {
            Schema::table('business_settings', function (Blueprint $table) {
                // Drop old single column if exists
                if (Schema::hasColumn('business_settings', 'charge_per_inquiry')) {
                    $table->dropColumn('charge_per_inquiry');
                }

                if (!Schema::hasColumn('business_settings', 'is_inquiry_charge_manual')) {
                    $table->boolean('is_inquiry_charge_manual')->default(false)->after('is_quotation_charge_manual');
                }
                if (!Schema::hasColumn('business_settings', 'charge_per_contact_us_inquiry')) {
                    $table->double('charge_per_contact_us_inquiry', 10, 2)->default(1.00)->after('is_inquiry_charge_manual');
                }
                if (!Schema::hasColumn('business_settings', 'charge_per_product_inquiry')) {
                    $table->double('charge_per_product_inquiry', 10, 2)->default(1.00)->after('charge_per_contact_us_inquiry');
                }
                if (!Schema::hasColumn('business_settings', 'charge_per_service_inquiry')) {
                    $table->double('charge_per_service_inquiry', 10, 2)->default(1.00)->after('charge_per_product_inquiry');
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
                $cols = [];
                if (Schema::hasColumn('business_settings', 'charge_per_service_inquiry')) {
                    $cols[] = 'charge_per_service_inquiry';
                }
                if (Schema::hasColumn('business_settings', 'charge_per_product_inquiry')) {
                    $cols[] = 'charge_per_product_inquiry';
                }
                if (Schema::hasColumn('business_settings', 'charge_per_contact_us_inquiry')) {
                    $cols[] = 'charge_per_contact_us_inquiry';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('business_categories')) {
            Schema::table('business_categories', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('business_categories', 'charge_per_service_inquiry')) {
                    $cols[] = 'charge_per_service_inquiry';
                }
                if (Schema::hasColumn('business_categories', 'charge_per_product_inquiry')) {
                    $cols[] = 'charge_per_product_inquiry';
                }
                if (Schema::hasColumn('business_categories', 'charge_per_contact_us_inquiry')) {
                    $cols[] = 'charge_per_contact_us_inquiry';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'charge_per_inquiry')) {
                    $table->double('charge_per_inquiry', 10, 2)->default(1.00);
                }
            });
        }
    }
};

