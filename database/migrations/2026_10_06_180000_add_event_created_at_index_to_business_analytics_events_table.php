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
        // 1. Optimize business_analytics_events indexes for analytics & duplicate checks
        Schema::table('business_analytics_events', function (Blueprint $table) {
            // Standalone index on created_at for MIN/MAX queries and chronological sorting
            $table->index('created_at', 'idx_analytics_created_at');

            // Covering index for global admin view counts, trends, and unique visitor metrics
            $table->index(['event', 'created_at', 'visitor_hash'], 'idx_analytics_event_created_visitor');

            // Composite index for entity/module breakdown across date windows
            $table->index(['event', 'page_type', 'created_at'], 'idx_analytics_event_page_created_at');

            // Composite index for business-scoped analytics (e.g. top products/services in period)
            $table->index(['business_id', 'event', 'page_type', 'created_at'], 'idx_analytics_biz_event_page_created');

            // High-frequency duplicate view check covering index (called on every request in trackView)
            $table->index(
                ['business_id', 'visitor_hash', 'page_type', 'page_id', 'created_at'],
                'idx_analytics_dup_check'
            );
        });

        // 2. Optimize products table (10,000+ rows) for slug lookups and status filtering
        Schema::table('products', function (Blueprint $table) {
            $table->index('slug', 'idx_products_slug');
            $table->index(['business_id', 'status'], 'idx_products_business_status');
        });

        // 3. Optimize services table for slug lookups and status filtering
        Schema::table('services', function (Blueprint $table) {
            $table->index('slug', 'idx_services_slug');
            $table->index(['business_id', 'status'], 'idx_services_business_status');
        });

        // 4. Optimize businesses table for frequent status filtering ('active', 'pending')
        Schema::table('businesses', function (Blueprint $table) {
            $table->index('status', 'idx_businesses_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_analytics_events', function (Blueprint $table) {
            $table->dropIndex('idx_analytics_created_at');
            $table->dropIndex('idx_analytics_event_created_visitor');
            $table->dropIndex('idx_analytics_event_page_created_at');
            $table->dropIndex('idx_analytics_biz_event_page_created');
            $table->dropIndex('idx_analytics_dup_check');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_slug');
            $table->dropIndex('idx_products_business_status');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_slug');
            $table->dropIndex('idx_services_business_status');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex('idx_businesses_status');
        });
    }
};
