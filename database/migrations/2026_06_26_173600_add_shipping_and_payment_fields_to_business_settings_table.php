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
        Schema::table('business_settings', function (Blueprint $table) {
            $table->boolean('show_delivery_charges')->default(true)->after('is_order_place_on_website');
            $table->decimal('same_state_shipping_charge_per_kg', 10, 2)->default(40)->after('show_delivery_charges');
            $table->decimal('other_state_shipping_charge_per_kg', 10, 2)->default(90)->after('same_state_shipping_charge_per_kg');
            $table->boolean('accept_online_payments')->default(false)->after('other_state_shipping_charge_per_kg');
            $table->enum('online_payment_method', ['manual_upi'])->default('manual_upi')->after('accept_online_payments');
            $table->string('upi_id')->nullable()->after('online_payment_method');
            $table->boolean('accept_cash_on_delivery')->default(true)->after('upi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_delivery_charges',
                'same_state_shipping_charge_per_kg',
                'other_state_shipping_charge_per_kg',
                'accept_online_payments',
                'online_payment_method',
                'upi_id',
                'payment_receive_upi_id',
            ]);
        });
    }
};
