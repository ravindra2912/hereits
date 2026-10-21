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
        Schema::create('business_transactions', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->unsignedBigInteger('business_id')->index();
            $table->enum('type', ['order'])->default('order');
            $table->string('source_id')->nullable()->index();
            $table->string('transaction_id')->unique()->nullable()->index();
            $table->string('transaction_screenshot')->nullable();
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
        Schema::dropIfExists('business_transactions');
    }
};
