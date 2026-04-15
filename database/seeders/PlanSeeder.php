<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Schema;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            // Subscription Plans
            [
                'name' => 'Premium Subscription',
                'plan_type' => 'subscription',
                'price' => 199,
                'per_product_price' => 1,
                'per_service_price' => 1,
                'max_product_limit' => 1,
                'max_service_limit' => 1,
                'duration' => 12,
                'description' => 'Ideal for growing businesses with advanced needs',
                'benefits' => 'business listing, appointment booking, limited product listing, limited service listing, whatsapp inquiry',
                'usage_type' => 'recurring',
                'usage_limit' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise Subscription',
                'plan_type' => 'subscription',
                'price' => 399,
                'per_product_price' => 1,
                'per_service_price' => 1,
                'max_product_limit' => 1,
                'max_service_limit' => 1,
                'duration' => 12,
                'description' => 'Complete solution for large enterprises',
                'benefits' => 'business listing, appointment booking, limited product listing, limited service listing, whatsapp inquiry, Profile setup',
                'usage_type' => 'recurring',
                'usage_limit' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Product Plans
            [
                'name' => 'Product Listing - Starter',
                'plan_type' => 'product',
                'price' => null,
                'per_product_price' => 1,
                'per_service_price' => 1,
                'max_product_limit' => 200,
                'max_service_limit' => 1,
                'duration' => 6,
                'description' => 'List your products and reach more customers',
                'benefits' => "List up to 200 products, Basic analytics, Standard visibility",
                'usage_type' => 'one_time',
                'usage_limit' => 200,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Product Listing - Pro',
                'plan_type' => 'product',
                'price' => null,
                'per_product_price' => 0.90,
                'per_service_price' => 1,
                'max_product_limit' => 500,
                'max_service_limit' => 1,
                'duration' => 12,
                'description' => 'Advanced product listing with premium features',
                'benefits' => "List up to 500 products, Basic analytics, Standard visibility",
                'usage_type' => 'unlimited',
                'usage_limit' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Service Plans
            [
                'name' => 'Service Provider - Basic',
                'plan_type' => 'service',
                'price' => null,
                'per_service_price' => 1,
                'max_service_limit' => 100,
                'per_product_price' => 0,
                'max_product_limit' => 0,
                'duration' => 6,
                'description' => 'Showcase your services to potential clients',
                'benefits' => "List up to 100 services, Basic analytics, Standard visibility",
                'usage_type' => 'recurring',
                'usage_limit' => 100,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Service Provider - Professional',
                'plan_type' => 'service',
                'price' => null,
                'per_service_price' => 0.90,
                'max_service_limit' => 200,
                'per_product_price' => 0,
                'max_product_limit' => 0,
                'duration' => 12,
                'description' => 'Complete service management solution',
                'benefits' => "List up to 200 services, Basic analytics, Standard visibility",
                'usage_type' => 'unlimited',
                'usage_limit' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Appointment Plans
            // [
            //     'name' => 'Appointment - Business',
            //     'plan_type' => 'appointment',
            //     'price' => null,
            //     'duration' => 6,
            //     'description' => 'Advanced appointment management for busy businesses',
            //     'benefits' => "Up to 500 appointments/month, Advanced calendar, SMS & Email reminders, Up to 5 Staff members, Custom booking forms, Waitlist management",
            //     'usage_type' => 'recurring',
            //     'usage_limit' => 500,
            //     'status' => 'active',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => 'Appointment - Enterprise',
            //     'plan_type' => 'appointment',
            //     'price' => null,
            //     'duration' => 12,
            //     'description' => 'Unlimited appointment scheduling with premium features',
            //     'benefits' => "Unlimited appointments, Multi-location support, Unlimited staff, Advanced reporting, API access, Custom integrations, Dedicated account manager",
            //     'usage_type' => 'unlimited',
            //     'usage_limit' => null,
            //     'status' => 'active',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ];

        DB::table('plans')->insert($plans);
    }
}
