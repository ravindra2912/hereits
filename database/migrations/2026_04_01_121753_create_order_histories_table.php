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
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'ready_to_deliver',
                'delivered',
                'canceled',
                'canceled_by_user'
            ])->default('pending')->index();
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // composite indexes
            $table->index(['business_id', 'order_id']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'changed_by']);
            $table->index(['business_id', 'created_at']);

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
