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
        // 1. Business Settings: Drop listing limit columns
        Schema::table('business_settings', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('business_settings', 'product_limit')) $columnsToDrop[] = 'product_limit';
            if (Schema::hasColumn('business_settings', 'product_limit_expiry_date')) $columnsToDrop[] = 'product_limit_expiry_date';
            if (Schema::hasColumn('business_settings', 'service_limit')) $columnsToDrop[] = 'service_limit';
            if (Schema::hasColumn('business_settings', 'service_limit_expiry_date')) $columnsToDrop[] = 'service_limit_expiry_date';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 2. Plans: Drop limit columns and update plan_type enum column/data
        Schema::table('plans', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('plans', 'max_product_limit')) $columnsToDrop[] = 'max_product_limit';
            if (Schema::hasColumn('plans', 'max_service_limit')) $columnsToDrop[] = 'max_service_limit';
            if (Schema::hasColumn('plans', 'per_product_price')) $columnsToDrop[] = 'per_product_price';
            if (Schema::hasColumn('plans', 'per_service_price')) $columnsToDrop[] = 'per_service_price';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        DB::table('plans')
            ->whereIn('plan_type', ['product', 'service', 'appointment'])
            ->update(['plan_type' => 'subscription']);

        DB::statement("ALTER TABLE plans MODIFY COLUMN plan_type ENUM('subscription') NOT NULL DEFAULT 'subscription'");

        // 3. Site Settings: Drop free limit columns
        Schema::table('site_settings', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('site_settings', 'free_product_limit')) $columnsToDrop[] = 'free_product_limit';
            if (Schema::hasColumn('site_settings', 'free_service_limit')) $columnsToDrop[] = 'free_service_limit';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 4. Purchases: Update plan_type enum column and map existing data (appointment -> credit, product/service -> subscription)
        DB::statement("ALTER TABLE purchases MODIFY COLUMN plan_type ENUM('subscription', 'credit', 'product', 'service', 'appointment') NOT NULL DEFAULT 'subscription'");

        DB::table('purchases')
            ->where('plan_type', 'appointment')
            ->update(['plan_type' => 'credit']);

        DB::table('purchases')
            ->whereIn('plan_type', ['product', 'service'])
            ->update(['plan_type' => 'subscription']);

        DB::statement("ALTER TABLE purchases MODIFY COLUMN plan_type ENUM('subscription', 'credit') NOT NULL DEFAULT 'subscription'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
