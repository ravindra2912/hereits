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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['created_user_id']);

            $table->foreign('customer_id')->references('id')->on('users')->noActionOnDelete();
            $table->foreign('created_user_id')->references('id')->on('users')->noActionOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);

            $table->foreign('item_id')->references('id')->on('products')->noActionOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);

            $table->foreign('item_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['created_user_id']);

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
