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
        Schema::create('credit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->enum('transaction_type', ['credit', 'debit']);
            $table->enum('type', ['order', 'appointment', 'quotation', 'lucky_draw', 'recharge', 'manual', 'other'])->default('other');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
            $table->index(['business_id', 'type']);
            $table->index(['business_id', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_histories');
    }
};
