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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->bigInteger('business_id')->nullable();
            $table->bigInteger('referrer_business_id')->nullable()->comment('identifier of the business where user registered');
            $table->string('first_name', 20);
            $table->string('last_name', 20);
            $table->string('email', 100)->unique()->index();
            $table->bigInteger('contact')->unique()->nullable();
            $table->string('google_key')->unique()->index()->nullable();
            $table->enum('role', ["Business", 'User'])->default('User');
            $table->string('profile', 150)->nullable();
            $table->date('dob')->nullable();
            $table->string('notification_token')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
