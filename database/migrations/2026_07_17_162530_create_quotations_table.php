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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->string('quotation_no')->index();
            $table->unsignedBigInteger('created_by_id')->nullable()->index();
            $table->unsignedBigInteger('business_customer_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('packing_charge', 10, 2)->default(0);
            $table->boolean('is_pf')->default(false);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->enum('status', ['pending', 'inprogress', 'ordered', 'cancel', 'expired'])->default('inprogress')->index();
            $table->enum('created_from', ['pos', 'inquiry'])->default('pos');
            $table->text('reason')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->noActionOnDelete();
            $table->foreign('business_customer_id')->references('id')->on('business_customers')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->noActionOnDelete();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id')->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('item_name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('qty')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
