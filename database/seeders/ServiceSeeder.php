<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Target business ID 1 or fetch first
        $business = Business::find(1);

        if (!$business) {
            $business = Business::first();
        }

        if (!$business) {
            $this->command->info('No business found. skipping service seeding.');
            return;
        }

        $categories = Category::where('type', 'Services')->pluck('id')->toArray();

        // Create 50 services
        for ($i = 0; $i < 50; $i++) {
            $name = $faker->catchPhrase;
            $priceType = $faker->randomElement(['FixPrice', 'PriceInRange', 'WithoutPrice']); // Assuming similar price logic for services
            $price = $faker->randomFloat(2, 500, 2000); // Services might be more expensive

            $serviceData = [
                'business_id' => $business->id,
                'category_id' => !empty($categories) ? $faker->randomElement($categories) : null,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(6),
                'description' => $faker->paragraph,
                'price_type' => $priceType,
                'status' => 'active',
                'image_url' => 'default_service.png',
            ];

            if ($priceType == 'FixPrice') {
                $serviceData['price'] = $price;
            } elseif ($priceType == 'PriceInRange') {
                $serviceData['min_price'] = $price;
                $serviceData['max_price'] = $price * 1.5;
            }

            Service::create($serviceData);
        }
    }
}
