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
        if (!Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code', 50)->nullable()->unique()->after('google_key');
            });
        }

        if (!Schema::hasColumn('businesses', 'user_referral_code')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('user_referral_code', 50)->nullable()->after('owner_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }

        if (Schema::hasColumn('businesses', 'code')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
