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
        if (Schema::hasTable('quotation_items')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->unsignedBigInteger('item_id')->nullable()->change();
                $table->foreign('item_id')->references('id')->on('products')->nullOnDelete();
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->unsignedBigInteger('item_id')->nullable()->change();
                $table->foreign('item_id')->references('id')->on('products')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->foreign('item_id')->references('id')->on('products')->noActionOnDelete();
            });
        }

        if (Schema::hasTable('quotation_items')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->foreign('item_id')->references('id')->on('products')->noActionOnDelete();
            });
        }
    }
};
