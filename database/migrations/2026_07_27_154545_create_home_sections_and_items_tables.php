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
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->enum('section_type', [
                'banner_custom',
                'slider_banner',
                'product_categories',
                'product_category_items',
                'service_categories',
                'service_category_items',
                'products',
                'services',
                'product_labels',
                'experts'
            ])->default('banner_custom');
            $table->string('title')->nullable();
            $table->unsignedTinyInteger('image_count')->nullable(); // 1, 2, 3, 4, 6
            $table->string('slider_banner')->nullable();
            $table->enum('ui_style', ['style_1', 'style_2', 'style_3'])->nullable();
            $table->enum('item_selection_type', ['manual', 'random'])->nullable()->default('random');
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['business_id', 'status', 'sort_order']);
        });

        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained('home_sections')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('cascade');
            $table->foreignId('label_id')->nullable()->constrained('labels')->onDelete('cascade');
            $table->foreignId('expert_id')->nullable()->constrained('experts')->onDelete('cascade');
            $table->string('image_path')->nullable();
            $table->string('redirect_url')->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['home_section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_sections');
    }
};
