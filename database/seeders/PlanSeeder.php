<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                'duration' => 12,
                'description' => 'Ideal for growing businesses with advanced needs',
                'benefits' => 'business listing, appointment booking, product listing, service listing, whatsapp inquiry',
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
                'duration' => 12,
                'description' => 'Complete solution for large enterprises',
                'benefits' => 'business listing, appointment booking, product listing, service listing, whatsapp inquiry, Profile setup',
                'usage_type' => 'recurring',
                'usage_limit' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
