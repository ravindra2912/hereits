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
        Schema::create('business_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->enum('type', ['product', 'product_category']);

            // product_id when type = product
            // category_id when type = product_category
            $table->unsignedBigInteger('item_id');

            $table->boolean('is_role_wise')->default(false);

            $table->json('role_wise_detail')->nullable();

            $table->enum('price_type', [
                'fixed_price',
                'discount',
                'percentage',
            ]);

            // Used when is_role_wise = false
            $table->decimal('value', 12, 2)->nullable();

            // show price on front with discount
            $table->boolean('is_price_reflected_on_front')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'business_id',
                'type',
                'item_id',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_offers');
    }
};
