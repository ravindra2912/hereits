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
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('lucky_draw_entries', 'for')) {
                $table->enum('for', ['other', 'order'])->default('other')->after('customer_contact');
            }
            if (!Schema::hasColumn('lucky_draw_entries', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('for');
                $table->index(['for', 'source_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            if (Schema::hasColumn('lucky_draw_entries', 'source_id')) {
                $table->dropIndex(['for', 'source_id']);
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('lucky_draw_entries', 'for')) {
                $table->dropColumn('for');
            }
        });
    }
};
