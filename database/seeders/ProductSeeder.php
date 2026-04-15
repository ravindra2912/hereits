<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Target business ID 1 as per typical workflow, or fetch first
        $business = Business::find(1);

        if (!$business) {
            // Fallback if ID 1 doesn't exist
            $business = Business::first();
        }

        if (!$business) {
            $this->command->info('No business found. skipping product seeding.');
            return;
        }

        $categories = Category::where('type', 'Products')->pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            $name = $faker->catchPhrase;
            $priceType = $faker->randomElement(['FixPrice', 'PriceInRange', 'WithoutPrice']);
            $price = $faker->randomFloat(2, 100, 5000);

            $productData = [
                'business_id' => $business->id,
                'category_id' => !empty($categories) ? $faker->randomElement($categories) : null,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(6),
                'description' => $faker->paragraph,
                'price_type' => $priceType,
                'status' => 'active',
            ];

            if ($priceType == 'FixPrice') {
                $productData['price'] = $price;
                $productData['sell_price'] = $price * 0.9; // 10% discount
            } elseif ($priceType == 'PriceInRange') {
                $productData['min_price'] = $price;
                $productData['max_price'] = $price * 1.5;
            }

            $product = Product::create($productData);

            // Add 1 to 3 images
            $limit = rand(1, 4);
            for ($j = 0; $j < $limit; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    // 'image_url' => 'https://placehold.co/600x400?text=' . urlencode($name . ' ' . $j),
                    'image_url' => 'default_product.png',
                    'sort_order' => $j
                ]);
            }
        }
    }
}
