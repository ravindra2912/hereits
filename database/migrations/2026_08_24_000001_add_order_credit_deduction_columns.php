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
        Schema::table('business_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('business_categories', 'deduct_credit_per_customer_order')) {
                $table->double('deduct_credit_per_customer_order', 10, 2)->default(1)->after('deduct_credit_per_self_appointment')->comment('Credits deducted when order is placed by customer');
            }
            if (!Schema::hasColumn('business_categories', 'deduct_credit_per_self_order')) {
                $table->double('deduct_credit_per_self_order', 10, 2)->default(1)->after('deduct_credit_per_customer_order')->comment('Credits deducted when order is placed by business/self');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'is_order_creadit_diduct_manual')) {
                $table->boolean('is_order_creadit_diduct_manual')->default(false)->after('deduct_credit_per_self_appointment')->comment('TRUE = deduct credit using business setting fields, FALSE = deduct credit category wise');
            }
            if (!Schema::hasColumn('business_settings', 'deduct_credit_per_customer_order')) {
                $table->double('deduct_credit_per_customer_order', 10, 2)->default(1)->after('is_order_creadit_diduct_manual')->comment('Credits deducted when order is placed by customer');
            }
            if (!Schema::hasColumn('business_settings', 'deduct_credit_per_self_order')) {
                $table->double('deduct_credit_per_self_order', 10, 2)->default(1)->after('deduct_credit_per_customer_order')->comment('Credits deducted when order is placed by business/self');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_categories', function (Blueprint $table) {
            if (Schema::hasColumn('business_categories', 'deduct_credit_per_customer_order')) {
                $table->dropColumn('deduct_credit_per_customer_order');
            }
            if (Schema::hasColumn('business_categories', 'deduct_credit_per_self_order')) {
                $table->dropColumn('deduct_credit_per_self_order');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'is_order_creadit_diduct_manual')) {
                $table->dropColumn('is_order_creadit_diduct_manual');
            }
            if (Schema::hasColumn('business_settings', 'deduct_credit_per_customer_order')) {
                $table->dropColumn('deduct_credit_per_customer_order');
            }
            if (Schema::hasColumn('business_settings', 'deduct_credit_per_self_order')) {
                $table->dropColumn('deduct_credit_per_self_order');
            }
        });
    }
};
