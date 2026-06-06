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
        Schema::create('user_credit_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->enum('type', ['credit', 'debit']);

            $table->decimal('amount', 10, 2)->default(0);

            $table->enum('reference_type', ['business_subscription', 'payout', 'admin_adjustment'])->default('business_subscription');

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('transaction_id')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credit_transactions');
    }
};
