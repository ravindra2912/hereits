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
        // 1. Update purchases table plan_type ENUM
        if (Schema::hasTable('purchases')) {
            // Step A: Temporarily expand ENUM to allow 'credit' alongside existing values
            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service', 'appointment', 'creadit', 'credit') NOT NULL");

            // Step B: Now safely migrate data to 'credit'
            DB::table('purchases')
                ->whereIn('plan_type', ['appointment', 'creadit'])
                ->update(['plan_type' => 'credit']);

            // Step C: Constrain ENUM to only the final valid values
            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service', 'credit') NOT NULL");
        }

        // 2. Modify ENUM on plans table to remove appointment / creadit
        if (Schema::hasTable('plans')) {
            DB::statement("ALTER TABLE `plans` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service') NOT NULL");
        }

        // 3. Update existing coupons replacing 'appointment' with 'credit' in applicable_for JSON
        if (Schema::hasTable('coupons')) {
            $coupons = DB::table('coupons')->whereNotNull('applicable_for')->get();
            foreach ($coupons as $coupon) {
                $applicable = json_decode($coupon->applicable_for, true);
                if (is_array($applicable) && in_array('appointment', $applicable)) {
                    $updated = array_values(array_unique(array_map(function ($type) {
                        return $type === 'appointment' ? 'credit' : $type;
                    }, $applicable)));

                    DB::table('coupons')->where('id', $coupon->id)->update([
                        'applicable_for' => json_encode($updated),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert coupons replacing 'credit' with 'appointment'
        if (Schema::hasTable('coupons')) {
            $coupons = DB::table('coupons')->whereNotNull('applicable_for')->get();
            foreach ($coupons as $coupon) {
                $applicable = json_decode($coupon->applicable_for, true);
                if (is_array($applicable) && in_array('credit', $applicable)) {
                    $updated = array_values(array_unique(array_map(function ($type) {
                        return $type === 'credit' ? 'appointment' : $type;
                    }, $applicable)));

                    DB::table('coupons')->where('id', $coupon->id)->update([
                        'applicable_for' => json_encode($updated),
                    ]);
                }
            }
        }

        // 2. Revert plans ENUM
        if (Schema::hasTable('plans')) {
            DB::statement("ALTER TABLE `plans` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service', 'appointment') NOT NULL");
        }

        // 3. Revert purchases ENUM and data
        if (Schema::hasTable('purchases')) {
            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service', 'appointment', 'credit') NOT NULL");

            DB::table('purchases')
                ->where('plan_type', 'credit')
                ->update(['plan_type' => 'appointment']);

            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `plan_type` ENUM('subscription', 'product', 'service', 'appointment') NOT NULL");
        }
    }
};
