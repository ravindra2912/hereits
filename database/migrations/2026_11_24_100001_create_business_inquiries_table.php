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
        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('contact', 20);
            $table->string('email', 150)->nullable();
            $table->text('message')->nullable();
            $table->enum('inquiry_type', ['contact_us', 'product', 'service'])->default('contact_us');
            $table->enum('system_status', ['pending', 'accepted', 'rejected', 'not_fulfilled'])->default('pending');
            $table->enum('business_status', ['new', 'contacted', 'follow_up', 'scheduled', 'converted', 'closed'])->default('new');
            $table->boolean('credit_deducted')->default(false);
            $table->foreignId('credit_deducted_id')->nullable()->constrained('credit_histories')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index('user_id');
            $table->index('assigned_to');
            $table->index('system_status');
            $table->index('business_status');
            $table->index('inquiry_type');
            $table->index(['business_id', 'system_status']);
            $table->index(['business_id', 'business_status']);
            $table->index(['business_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_inquiries');
    }
};

