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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('plan_type', ['subscription'])->default('subscription');
            $table->decimal('price', 10, 2)->nullable();
            $table->double('per_product_price', 10, 2)->nullable()->comment('Price of one product');
            $table->double('per_service_price', 10, 2)->nullable()->comment('Price of one service');
            $table->integer('max_product_limit')->nullable()->comment('Max limit of products');
            $table->integer('max_service_limit')->nullable()->comment('Max limit of services');
            $table->integer('duration')->nullable()->comment('Duration in months, NULL = unlimited');
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->enum('usage_type', ['one_time', 'recurring', 'unlimited'])->default('unlimited');
            $table->integer('usage_limit')->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
