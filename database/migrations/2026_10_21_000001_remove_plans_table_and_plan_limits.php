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
        // 1. Reactivate any products and services that were in-activated due to plan expiry
        if (Schema::hasTable('products')) {
            DB::table('products')
                ->where('status', 'in-active-expiry')
                ->update(['status' => 'active']);
        }

        if (Schema::hasTable('services')) {
            DB::table('services')
                ->where('status', 'in-active-expiry')
                ->update(['status' => 'active']);
        }

        // 2. Drop foreign key on purchases.plan_id before dropping plans table
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                // Drop foreign key if it exists
                try {
                    $table->dropForeign(['plan_id']);
                } catch (\Exception $e) {
                    // Foreign key might already be dropped or have different name
                }
            });
        }

        // 3. Drop plans table
        Schema::dropIfExists('plans');

        // 4. Drop free_product_limit and free_service_limit from site_settings if they exist
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('site_settings', 'free_product_limit')) {
                    $columnsToDrop[] = 'free_product_limit';
                }
                if (Schema::hasColumn('site_settings', 'free_service_limit')) {
                    $columnsToDrop[] = 'free_service_limit';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        // 5. Drop subscription_expiry_date, product_limit, product_limit_expiry_date, service_limit, service_limit_expiry_date from business_settings
        if (Schema::hasTable('business_settings')) {
            Schema::table('business_settings', function (Blueprint $table) {
                $bsColumnsToDrop = [];
                $targetColumns = [
                    'subscription_expiry_date',
                    'product_limit',
                    'product_limit_expiry_date',
                    'service_limit',
                    'service_limit_expiry_date',
                ];
                foreach ($targetColumns as $col) {
                    if (Schema::hasColumn('business_settings', $col)) {
                        $bsColumnsToDrop[] = $col;
                    }
                }
                if (!empty($bsColumnsToDrop)) {
                    $table->dropColumn($bsColumnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('plan_type', ['subscription', 'product', 'service'])->default('subscription');
                $table->decimal('price', 10, 2)->nullable();
                $table->double('per_product_price', 10, 2)->nullable();
                $table->double('per_service_price', 10, 2)->nullable();
                $table->integer('max_product_limit')->nullable();
                $table->integer('max_service_limit')->nullable();
                $table->integer('duration')->nullable();
                $table->text('description')->nullable();
                $table->text('benefits')->nullable();
                $table->enum('usage_type', ['one_time', 'recurring', 'unlimited'])->default('unlimited');
                $table->integer('usage_limit')->nullable();
                $table->enum('status', ['active', 'in-active'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'free_product_limit')) {
                    $table->integer('free_product_limit')->nullable()->default(20);
                }
                if (!Schema::hasColumn('site_settings', 'free_service_limit')) {
                    $table->integer('free_service_limit')->nullable()->default(10);
                }
            });
        }

        if (Schema::hasTable('business_settings')) {
            Schema::table('business_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('business_settings', 'subscription_expiry_date')) {
                    $table->date('subscription_expiry_date')->nullable();
                }
                if (!Schema::hasColumn('business_settings', 'product_limit')) {
                    $table->bigInteger('product_limit')->default(0);
                }
                if (!Schema::hasColumn('business_settings', 'product_limit_expiry_date')) {
                    $table->date('product_limit_expiry_date')->nullable();
                }
                if (!Schema::hasColumn('business_settings', 'service_limit')) {
                    $table->bigInteger('service_limit')->default(0);
                }
                if (!Schema::hasColumn('business_settings', 'service_limit_expiry_date')) {
                    $table->date('service_limit_expiry_date')->nullable();
                }
            });
        }
    }
};
