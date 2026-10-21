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
        Schema::table('favorites', function (Blueprint $table) {
            $table->string('favorite_type', 50)->change();
            $table->index(['user_id', 'favorite_type', 'favorite_item_id'], 'favorites_user_type_item_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex('favorites_user_type_item_index');
            $table->enum('favorite_type', ['business', 'expert'])->change();
        });
    }
};
