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
        Schema::table('business_customers', function (Blueprint $table) {
            $table->foreignId('customer_role_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('business_customer_roles')
                ->nullOnDelete();

            $table->index(['business_id', 'customer_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_customers', function (Blueprint $table) {
            $table->dropForeign(['customer_role_id']);
            $table->dropIndex(['business_id', 'customer_role_id']);
            $table->dropColumn('customer_role_id');
        });
    }
};
