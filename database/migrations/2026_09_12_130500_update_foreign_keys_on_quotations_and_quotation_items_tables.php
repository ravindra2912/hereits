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
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['order_id']);

            $table->foreign('created_by_id')->references('id')->on('users')->noActionOnDelete();
            $table->foreign('customer_id')->references('id')->on('users')->noActionOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->noActionOnDelete();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->unsignedBigInteger('item_id')->nullable()->change();
            if (!Schema::hasColumn('quotation_items', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->foreign('item_id')->references('id')->on('products')->noActionOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            if (Schema::hasColumn('quotation_items', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            $table->foreign('item_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['order_id']);

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }
};
