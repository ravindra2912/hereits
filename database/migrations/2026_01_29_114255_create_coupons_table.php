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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('Influencer_business_id')->nullable();
            $table->enum('discount_type', ['flat', 'percentage']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Max discount for percentage type');
            $table->decimal('min_purchase', 10, 2)->nullable()->comment('Minimum purchase amount');
            $table->enum('usage_type', ['one_time', 'recurring', 'unlimited'])->default('one_time');
            $table->integer('usage_limit')->default(1)->comment('Total usage limit');
            $table->boolean('is_limit_per_business')->default(false)->comment('Is limit per business');
            $table->integer('usage_limit_per_business')->default(1)->comment('Total usage limit per business');
            $table->integer('usage_count')->default(0)->comment('Current usage count');
            $table->json('applicable_for')->nullable()->comment('all, subscription, product, service, appointment');
            $table->boolean('is_for_specific_business')->default(false);
            $table->json('business_ids')->nullable()->comment('Array of business IDs');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'in-active', 'expired'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('Influencer_business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
