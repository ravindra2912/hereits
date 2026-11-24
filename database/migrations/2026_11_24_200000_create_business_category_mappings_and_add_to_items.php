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
        // 1. Pivot table for multiple business categories
        if (!Schema::hasTable('business_category_mappings')) {
            Schema::create('business_category_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('business_category_id');
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->cascadeOnDelete();
                $table->unique(['business_id', 'business_category_id'], 'uq_biz_cat_mapping');
            });
        }

        // Backfill existing businesses into mapping table
        $existingBusinesses = DB::table('businesses')->whereNotNull('business_category_id')->select('id', 'business_category_id', 'created_at', 'updated_at')->get();
        foreach ($existingBusinesses as $b) {
            DB::table('business_category_mappings')->insertOrIgnore([
                'business_id' => $b->id,
                'business_category_id' => $b->business_category_id,
                'created_at' => $b->created_at ?? now(),
                'updated_at' => $b->updated_at ?? now(),
            ]);
        }

        // 2. Add business_category_id to products
        if (!Schema::hasColumn('products', 'business_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('business_category_id')->nullable()->after('business_id')->index();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->nullOnDelete();
            });

            // Backfill products with parent business's category
            DB::statement("UPDATE products p JOIN businesses b ON p.business_id = b.id SET p.business_category_id = b.business_category_id WHERE p.business_category_id IS NULL AND b.business_category_id IS NOT NULL");
        }

        // 3. Add business_category_id to services
        if (!Schema::hasColumn('services', 'business_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('business_category_id')->nullable()->after('business_id')->index();
                $table->foreign('business_category_id')->references('id')->on('business_categories')->nullOnDelete();
            });

            // Backfill services with parent business's category
            DB::statement("UPDATE services s JOIN businesses b ON s.business_id = b.id SET s.business_category_id = b.business_category_id WHERE s.business_category_id IS NULL AND b.business_category_id IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'business_category_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['business_category_id']);
                $table->dropColumn('business_category_id');
            });
        }

        if (Schema::hasColumn('products', 'business_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['business_category_id']);
                $table->dropColumn('business_category_id');
            });
        }

        Schema::dropIfExists('business_category_mappings');
    }
};

