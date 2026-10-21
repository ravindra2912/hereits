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
        Schema::create('lucky_draw_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_draw_id')->constrained('lucky_draws')->onDelete('cascade');
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->decimal('win_percentage', 5, 2)->default(0.00);
            $table->integer('total_won')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lucky_draw_prizes');
    }
};
