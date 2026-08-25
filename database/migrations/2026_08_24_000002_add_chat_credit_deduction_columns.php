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
        Schema::table('business_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('business_categories', 'deduct_credit_per_chat')) {
                $table->double('deduct_credit_per_chat', 10, 2)->default(1.00)->after('deduct_credit_per_self_order');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'is_chat_creadit_diduct_manual')) {
                $table->boolean('is_chat_creadit_diduct_manual')->default(false)->after('is_order_creadit_diduct_manual');
            }
            if (!Schema::hasColumn('business_settings', 'deduct_credit_per_chat')) {
                $table->double('deduct_credit_per_chat', 10, 2)->default(1.00)->after('deduct_credit_per_self_order');
            }
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'business_unlocked_until')) {
                $table->timestamp('business_unlocked_until')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_categories', function (Blueprint $table) {
            if (Schema::hasColumn('business_categories', 'deduct_credit_per_chat')) {
                $table->dropColumn('deduct_credit_per_chat');
            }
        });

        Schema::table('business_settings', function (Blueprint $table) {
            if (Schema::hasColumn('business_settings', 'is_chat_creadit_diduct_manual')) {
                $table->dropColumn('is_chat_creadit_diduct_manual');
            }
            if (Schema::hasColumn('business_settings', 'deduct_credit_per_chat')) {
                $table->dropColumn('deduct_credit_per_chat');
            }
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'business_unlocked_until')) {
                $table->dropColumn('business_unlocked_until');
            }
        });
    }
};
