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
            $table->unsignedBigInteger('business_customer_id')->nullable()->after('customer_id')->index();
            $table->foreign('business_customer_id')->references('id')->on('business_customers')->nullOnDelete();
        });

        // Backfill existing quotation orders with business_customer_id and customer_id
        $quotations = \Illuminate\Support\Facades\DB::table('quotations')
            ->whereNotNull('order_id')
            ->whereNotNull('business_customer_id')
            ->get();

        foreach ($quotations as $q) {
            $bc = \Illuminate\Support\Facades\DB::table('business_customers')->where('id', $q->business_customer_id)->first();
            $update = ['business_customer_id' => $q->business_customer_id];
            if ($bc && $bc->customer_id) {
                $order = \Illuminate\Support\Facades\DB::table('orders')->where('id', $q->order_id)->first();
                if ($order && !$order->customer_id) {
                    $update['customer_id'] = $bc->customer_id;
                }
            }
            \Illuminate\Support\Facades\DB::table('orders')->where('id', $q->order_id)->update($update);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['business_customer_id']);
            $table->dropColumn('business_customer_id');
        });
    }
};
