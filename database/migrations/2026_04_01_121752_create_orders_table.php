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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 🔹 Foreign keys (already indexed or should be)
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('created_user_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // 🔹 Frequently searched
            $table->string('invoice_number')->nullable()->index();

            // 🔹 Order filters (VERY IMPORTANT for dashboard queries)
            $table->enum('order_source', ['web', 'pos'])->index();
            $table->enum('order_type', ['delivery', 'pickup', 'in_store'])->index();

            $table->string('customer_name'); // search/filter
            $table->string('customer_contact')->nullable();

            // 🔹 Location-based filters (optional but useful)
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();

            // 🔹 Financials (no index needed normally)
            $table->decimal('igst', 10, 2)->default(0);
            $table->decimal('sgst', 10, 2)->default(0);
            $table->decimal('cgst', 10, 2)->default(0);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            // 🔹 Status filters (VERY COMMON in admin panels)
            $table->enum('payment_method', ['cash', 'upi', 'card', 'online'])->index();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();

            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'ready_to_deliver',
                'delivered',
                'canceled',
                'canceled_by_user'
            ])->default('pending')->index();

            $table->timestamps();
            $table->softDeletes();

            // 🔥 Composite indexes (VERY POWERFUL)
            $table->index(['business_id', 'order_status']); // dashboard filtering
            $table->index(['business_id', 'payment_status']);
            $table->index(['business_id', 'created_at']); // reports
            $table->index(['customer_id', 'created_at']); // customer history

            // 🔹 Foreign keys
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
