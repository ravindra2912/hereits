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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('slug');
            $table->text('image_url')->nullable();
            $table->enum('price_type', ['FixPrice', 'PriceInRange', 'WithoutPrice'])->default('FixPrice');
            $table->double('price')->nullable();
            $table->double('min_price')->nullable();
            $table->double('max_price')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
