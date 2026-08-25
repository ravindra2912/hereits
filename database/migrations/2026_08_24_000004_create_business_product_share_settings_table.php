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
        Schema::create('business_product_share_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('target_business_id')->constrained('businesses')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['source_business_id', 'target_business_id', 'status'], 'idx_biz_product_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_product_share_settings');
    }
};
