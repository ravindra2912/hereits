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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id()->startingValue(20);
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('plan_id')->index()->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
            $table->bigInteger('coupon_id')->nullable();
            $table->enum('plan_type', ['subscription', 'product', 'service', 'appointment']);

            $table->decimal('subtotal', 10, 2);                              // 🔥 subtotal amount
            $table->integer('quantity')->nullable();                      // 🔥 products limit/services limit/appoinment credit
            $table->decimal('coupon_discount_amount', 10, 2)->default(0);
            $table->decimal('activated_plan_discount', 10, 2)->default(0);
            $table->double('sgst')->nullable();
            $table->double('cgst')->nullable();
            $table->double('igst')->nullable();
            $table->decimal('total_amount', 10, 2);                       // 🔥 final billed amount

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('plan_status', ['pending', 'active', 'expired', 'override'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
