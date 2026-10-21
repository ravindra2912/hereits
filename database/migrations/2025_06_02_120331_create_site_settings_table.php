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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->double('per_credit_price', 10, 2)->default(1)->comment('Price of one credit');
            $table->integer('free_credit')->default(30)->comment('Free initial credit for business signup');
            $table->double('charge_place_order_on_website', 10, 2)->default(0.1)->comment('Charge for placing order on website');
            $table->double('charge_place_order_on_pos', 10, 2)->default(0.05)->comment('Charge for placing order on pos');
            $table->double('draw_credit_per_scratch', 10, 2)->default(1)->comment('Credits deducted per scratch card');
            $table->double('charge_per_quotation', 10, 2)->default(1)->comment('Credits charged per quotation');
            $table->string('payment_gateway', 20)->default('cashfree')->comment('default payment gateway');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
