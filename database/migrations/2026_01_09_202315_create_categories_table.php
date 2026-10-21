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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('type', ['Services', 'Products']);
            $table->integer('sort_order')->default(0);
            $table->boolean('show_in_home')->default(false);
            $table->boolean('show_in_home_with_items')->default(false);
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index('slug', 'idx_categories_slug');
            $table->index(['business_id', 'status'], 'idx_categories_biz_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
