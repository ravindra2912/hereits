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
        Schema::table('otp_management', function (Blueprint $table) {
            if (!Schema::hasColumn('otp_management', 'type')) {
                $table->enum('type', ['user', 'business'])->default('business')->after('source')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_management', function (Blueprint $table) {
            if (Schema::hasColumn('otp_management', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
