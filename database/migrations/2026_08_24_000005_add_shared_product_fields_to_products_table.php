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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'parent_product_id')) {
                $table->unsignedBigInteger('parent_product_id')->nullable()->after('business_id');
            }
            if (!Schema::hasColumn('products', 'parent_product_business_id')) {
                $table->unsignedBigInteger('parent_product_business_id')->nullable()->after('parent_product_id');
            }
            if (!Schema::hasColumn('products', 'share_type')) {
                $table->enum('share_type', ['shared', 'copied'])->nullable()->after('parent_product_business_id');
            }

            $table->index(['parent_product_id', 'parent_product_business_id', 'share_type'], 'idx_products_shared_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_shared_parent');
            $table->dropColumn(['parent_product_id', 'parent_product_business_id', 'share_type']);
        });
    }
};
