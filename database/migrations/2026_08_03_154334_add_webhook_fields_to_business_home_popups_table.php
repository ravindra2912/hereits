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
        Schema::table('business_home_popups', function (Blueprint $table) {
            $table->boolean('is_web_hook_for_google_sheet')->default(false)->after('status');
            $table->string('google_sheet_webhook_url')->nullable()->after('is_web_hook_for_google_sheet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_home_popups', function (Blueprint $table) {
            $table->dropColumn(['is_web_hook_for_google_sheet', 'google_sheet_webhook_url']);
        });
    }
};
