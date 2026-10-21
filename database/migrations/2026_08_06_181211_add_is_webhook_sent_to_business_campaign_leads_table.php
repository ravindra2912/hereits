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
        Schema::table('business_campaign_leads', function (Blueprint $table) {
            $table->boolean('is_webhook_sent')->default(false)->after('status');
            $table->text('webhook_response')->nullable()->after('is_webhook_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_campaign_leads', function (Blueprint $table) {
            $table->dropColumn('is_webhook_sent');
            $table->dropColumn('webhook_response');
        });
    }
};
