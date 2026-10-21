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
        // 1. Optimize cities table (47,000+ rows) for state-based lookups and status filtering
        if (Schema::hasTable('cities')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->index(['state_id', 'status'], 'idx_cities_state_status');
            });
        }

        // 2. Optimize users table for business relation and role/status lookups
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('business_id', 'idx_users_business_id');
                $table->index(['role', 'status'], 'idx_users_role_status');
                $table->index('referrer_business_id', 'idx_users_referrer_biz_id');
            });
        }

        // 3. Optimize categories table for slug lookups and business-status filters
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug', 'idx_categories_slug');
                $table->index(['business_id', 'status'], 'idx_categories_biz_status');
            });
        }

        // 4. Optimize labels table for slug lookups and status filtering
        if (Schema::hasTable('labels')) {
            Schema::table('labels', function (Blueprint $table) {
                $table->index('slug', 'idx_labels_slug');
                $table->index('status', 'idx_labels_status');
            });
        }

        // 5. Optimize review_and_ratings table for polymorphic reviews (expert, business, etc.)
        if (Schema::hasTable('review_and_ratings')) {
            Schema::table('review_and_ratings', function (Blueprint $table) {
                $table->index(['review_type', 'review_on_id'], 'idx_reviews_type_on_id');
            });
        }

        // 6. Optimize appointment_bookings table for business date scheduling and status filters
        if (Schema::hasTable('appointment_bookings')) {
            Schema::table('appointment_bookings', function (Blueprint $table) {
                $table->index(['business_id', 'booking_date'], 'idx_bookings_biz_date');
                $table->index(['business_id', 'status'], 'idx_bookings_biz_status');
                $table->index('status', 'idx_bookings_status');
            });
        }

        // 7. Optimize products table for shared parent product relationships
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('parent_product_id', 'idx_products_parent_product_id');
                $table->index('parent_product_business_id', 'idx_products_parent_biz_id');
            });
        }

        // 8. Optimize purchases and transactions tables for status and coupon lookups
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index('coupon_id', 'idx_purchases_coupon_id');
                $table->index('status', 'idx_purchases_status');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('status', 'idx_transactions_status');
            });
        }

        // 9. Optimize business categories, experts, and states tables
        if (Schema::hasTable('business_categories')) {
            Schema::table('business_categories', function (Blueprint $table) {
                $table->index('status', 'idx_biz_categories_status');
            });
        }

        if (Schema::hasTable('experts')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->index(['business_id', 'status'], 'idx_experts_biz_status');
            });
        }

        if (Schema::hasTable('states')) {
            Schema::table('states', function (Blueprint $table) {
                $table->index('status', 'idx_states_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cities')) {
            Schema::table('cities', function (Blueprint $table) {
                $table->dropIndex('idx_cities_state_status');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_business_id');
                $table->dropIndex('idx_users_role_status');
                $table->dropIndex('idx_users_referrer_biz_id');
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('idx_categories_slug');
                $table->dropIndex('idx_categories_biz_status');
            });
        }

        if (Schema::hasTable('labels')) {
            Schema::table('labels', function (Blueprint $table) {
                $table->dropIndex('idx_labels_slug');
                $table->dropIndex('idx_labels_status');
            });
        }

        if (Schema::hasTable('review_and_ratings')) {
            Schema::table('review_and_ratings', function (Blueprint $table) {
                $table->dropIndex('idx_reviews_type_on_id');
            });
        }

        if (Schema::hasTable('appointment_bookings')) {
            Schema::table('appointment_bookings', function (Blueprint $table) {
                $table->dropIndex('idx_bookings_biz_date');
                $table->dropIndex('idx_bookings_biz_status');
                $table->dropIndex('idx_bookings_status');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_parent_product_id');
                $table->dropIndex('idx_products_parent_biz_id');
            });
        }

        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex('idx_purchases_coupon_id');
                $table->dropIndex('idx_purchases_status');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_status');
            });
        }

        if (Schema::hasTable('business_categories')) {
            Schema::table('business_categories', function (Blueprint $table) {
                $table->dropIndex('idx_biz_categories_status');
            });
        }

        if (Schema::hasTable('experts')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->dropIndex('idx_experts_biz_status');
            });
        }

        if (Schema::hasTable('states')) {
            Schema::table('states', function (Blueprint $table) {
                $table->dropIndex('idx_states_status');
            });
        }
    }
};
