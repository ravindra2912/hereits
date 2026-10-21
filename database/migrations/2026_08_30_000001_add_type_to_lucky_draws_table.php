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
        Schema::table('lucky_draws', function (Blueprint $table) {
            if (!Schema::hasColumn('lucky_draws', 'type')) {
                $table->enum('type', ['scratch', 'spinner'])->default('scratch')->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lucky_draws', function (Blueprint $table) {
            if (Schema::hasColumn('lucky_draws', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
