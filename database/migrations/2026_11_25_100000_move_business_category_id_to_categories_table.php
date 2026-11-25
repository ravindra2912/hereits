<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add business_category_id to categories table
        if (!Schema::hasColumn('categories', 'business_category_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('business_category_id')->nullable()->after('business_id')->index();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->nullOnDelete();
            });
        }

        // 2. Data backfill: populate categories.business_category_id from products/services or business
        if (Schema::hasColumn('products', 'business_category_id')) {
            DB::statement("UPDATE categories c JOIN products p ON c.id = p.category_id SET c.business_category_id = p.business_category_id WHERE c.business_category_id IS NULL AND p.business_category_id IS NOT NULL");
        }
        if (Schema::hasColumn('services', 'business_category_id')) {
            DB::statement("UPDATE categories c JOIN services s ON c.id = s.category_id SET c.business_category_id = s.business_category_id WHERE c.business_category_id IS NULL AND s.business_category_id IS NOT NULL");
        }
        DB::statement("UPDATE categories c JOIN businesses b ON c.business_id = b.id SET c.business_category_id = b.business_category_id WHERE c.business_category_id IS NULL AND b.business_category_id IS NOT NULL");

        // 3. Drop business_category_id from products table
        if (Schema::hasColumn('products', 'business_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['business_category_id']);
                $table->dropColumn('business_category_id');
            });
        }

        // 4. Drop business_category_id from services table
        if (Schema::hasColumn('services', 'business_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['business_category_id']);
                $table->dropColumn('business_category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add to products
        if (!Schema::hasColumn('products', 'business_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('business_category_id')->nullable()->after('business_id')->index();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->nullOnDelete();
            });

            DB::statement("UPDATE products p JOIN categories c ON p.category_id = c.id SET p.business_category_id = c.business_category_id WHERE c.business_category_id IS NOT NULL");
        }

        // Re-add to services
        if (!Schema::hasColumn('services', 'business_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('business_category_id')->nullable()->after('business_id')->index();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->nullOnDelete();
            });

            DB::statement("UPDATE services s JOIN categories c ON s.category_id = c.id SET s.business_category_id = c.business_category_id WHERE c.business_category_id IS NOT NULL");
        }

        // Drop from categories
        if (Schema::hasColumn('categories', 'business_category_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['business_category_id']);
                $table->dropColumn('business_category_id');
            });
        }
    }
};

