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
        if (!Schema::hasColumn('home_section_items', 'status')) {
            Schema::table('home_section_items', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('redirect_url');
                $table->index(['home_section_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('home_section_items', 'status')) {
            Schema::table('home_section_items', function (Blueprint $table) {
                $table->dropIndex(['home_section_id', 'status']);
                $table->dropColumn('status');
            });
        }
    }
};
