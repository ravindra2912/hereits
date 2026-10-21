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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->json('label_id')->nullable();
            $table->unsignedBigInteger('parent_product_id')->nullable();
            $table->unsignedBigInteger('parent_product_business_id')->nullable();
            $table->enum('share_type', ['shared', 'copied'])->nullable();
            $table->text('description')->nullable();
            $table->string('sku', 100)->nullable()->index();
            $table->string('slug')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('price_type', ['FixPrice', 'PriceInRange', 'WithoutPrice'])->default('FixPrice');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('parent_product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('parent_product_business_id')->references('id')->on('businesses')->nullOnDelete();

            $table->index('parent_product_id', 'idx_products_parent_product_id');
            $table->index('parent_product_business_id', 'idx_products_parent_biz_id');
            $table->index('slug', 'idx_products_slug');
            $table->index(['business_id', 'status'], 'idx_products_business_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
