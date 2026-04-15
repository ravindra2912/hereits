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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('purchase_id')->nullable()->index();
            $table->string('payment_id')->unique()->nullable()->index();  // gateway payment id
            $table->string('payment_screen_shot')->nullable();  // payment screen shot
            $table->string('order_id')->nullable()->index();              // gateway order id
            $table->double('amount', 10, 2);
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->enum('payment_type', ['cash', 'online'])->default('online');
            $table->string('payment_gateway')->default('cashfree');
            $table->json('gateway_response')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->dateTime('refund_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded_requested', 'refunded'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
