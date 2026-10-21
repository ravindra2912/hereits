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
        Schema::create('lucky_draw_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_draw_id')->constrained('lucky_draws')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_contact');
            $table->string('token', 64)->unique();
            $table->integer('credits_deducted')->default(1);
            $table->foreignId('won_prize_id')->nullable()->constrained('lucky_draw_prizes')->onDelete('set null');
            $table->integer('scratched_card_index')->nullable();
            $table->enum('status', ['pending', 'scratched', 'claimed'])->default('pending');
            $table->timestamp('scratched_at')->nullable();
            $table->timestamps();

            $table->index(['lucky_draw_id', 'status']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lucky_draw_entries');
    }
};
