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
        Schema::create('business_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('image', 150)->nullable();
            $table->string('slug', 50)->unique();
            $table->double('deduct_credit_per_customer_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by customer');
            $table->double('deduct_credit_per_self_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by business/self');
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_categories');
    }
};
