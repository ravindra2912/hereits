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
        // 1. Business Settings: Drop subscription_expiry_date
        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'subscription_expiry_date')) {
                $table->dropColumn('subscription_expiry_date');
            }
        });

        // 2. Purchases: Drop foreign key, plan_id column, activated_plan_discount, and adjust plan_type enum
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'plan_id')) {
                try {
                    $table->dropForeign(['plan_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                }
                $table->dropColumn('plan_id');
            }

            if (Schema::hasColumn('purchases', 'activated_plan_discount')) {
                $table->dropColumn('activated_plan_discount');
            }
        });

        // Update any non-credit plan_type in purchases to credit
        DB::table('purchases')
            ->where('plan_type', '!=', 'credit')
            ->update(['plan_type' => 'credit']);

        DB::statement("ALTER TABLE purchases MODIFY COLUMN plan_type ENUM('credit') NOT NULL DEFAULT 'credit'");

        // 3. User Credit Transactions: Update reference_type enum
        DB::table('user_credit_transactions')
            ->where('reference_type', 'business_subscription')
            ->update(['reference_type' => 'admin_adjustment']);

        DB::statement("ALTER TABLE user_credit_transactions MODIFY COLUMN reference_type ENUM('payout', 'admin_adjustment') NOT NULL DEFAULT 'admin_adjustment'");

        // 4. Drop plans table
        Schema::dropIfExists('plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
