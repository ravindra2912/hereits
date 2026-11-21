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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_contact_verify')) {
                $table->boolean('is_contact_verify')->default(0)->after('contact')->comment('1 = verified, 0 = unverified');
            }
        });

        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'is_contact_verify')) {
                $table->boolean('is_contact_verify')->default(0)->after('contact')->comment('1 = verified, 0 = unverified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_contact_verify')) {
                $table->dropColumn('is_contact_verify');
            }
        });

        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'is_contact_verify')) {
                $table->dropColumn('is_contact_verify');
            }
        });
    }
};

