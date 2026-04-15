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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->bigInteger('owner_id')->unsigned()->nullable()->index();
            $table->string('template', 20)->nullable();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->enum('business_type', ['Service', 'Product', 'Appointment'])->default('Service');
            $table->unsignedBigInteger('business_category_id')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('area', 30)->nullable();
            $table->string('pincode', 6)->nullable();
            $table->bigInteger('contact')->unique()->nullable();
            $table->string('business_image', 150)->nullable();
            $table->string('business_logo', 150)->nullable();
            $table->enum('status', ['pending', 'active', 'in-active', 'baned'])->default('pending');
            $table->float('rating', 2, 1)->default(0);
            $table->string('seo_description', 160)->nullable();
            $table->string('seo_keyword', 255)->nullable();
            $table->string('facebook', 150)->nullable();
            $table->string('twitter', 150)->nullable();
            $table->string('instagram', 150)->nullable();
            $table->string('linkedin', 150)->nullable();
            $table->string('youtube', 150)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('business_category_id')->references('id')->on('business_categories')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
