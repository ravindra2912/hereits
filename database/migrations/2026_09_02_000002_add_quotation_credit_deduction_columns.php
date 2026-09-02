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
            if (!Schema::hasColumn('business_categories', 'deduct_credit_per_quotation')) {
                $table->double('deduct_credit_per_quotation', 10, 2)->default(1.00)->after('deduct_credit_per_chat');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'is_quotation_creadit_diduct_manual')) {
                $table->tinyInteger('is_quotation_creadit_diduct_manual')->default(0)->after('is_chat_creadit_diduct_manual');
            }
            if (!Schema::hasColumn('business_settings', 'deduct_credit_per_quotation')) {
                $table->double('deduct_credit_per_quotation', 10, 2)->default(1.00)->after('deduct_credit_per_chat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_categories', function (Blueprint $table) {
            if (Schema::hasColumn('business_categories', 'deduct_credit_per_quotation')) {
                $table->dropColumn('deduct_credit_per_quotation');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'is_quotation_creadit_diduct_manual')) {
                $table->dropColumn('is_quotation_creadit_diduct_manual');
            }
            if (Schema::hasColumn('business_settings', 'deduct_credit_per_quotation')) {
                $table->dropColumn('deduct_credit_per_quotation');
            }
        });
    }
};
