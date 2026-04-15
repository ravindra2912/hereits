<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCategories = [
            'Fashion & Apparel',
            'Footwear',
            'Beauty & Personal Care',
            'Health & Wellness',
            'Electronics',
            'Home & Kitchen',
            'Grocery & Daily Needs',
            'Jewelry & Accessories',
            'Baby & Kids',
            'Sports & Fitness',
            'Books & Stationery',
            'Automotive',
            'Gifts & Occasions',
            'Tools & Hardware',
            'Furniture & Home Improvement',
            'Pet Supplies',
            'Agriculture & Garden',
            'Industrial & B2B Supplies',
            'Food & Beverages',
            'Digital Products',
        ];

        $serviceCategories = [
            'Beauty & Grooming Services',
            'Health & Medical Services',
            'Home Services',
            'Repair & Maintenance Services',
            'Professional Services',
            'Education & Training Services',
            'Event & Entertainment Services',
            'IT & Digital Services',
            'Business Support Services',
            'Automobile Services',
            'Travel & Tourism Services',
            'Real Estate Services',
            'Fitness & Wellness Services',
            'Cleaning & Hygiene Services',
            'Security & Safety Services',
            'Logistics & Transport Services',
            'Construction & Renovation Services',
            'Legal & Compliance Services',
            'Agriculture & Farm Services',
            'Personal & Lifestyle Services',
        ];

        $directory = 'category_images';

        foreach ($productCategories as $name) {
            // $imageName = $this->downloadAndStoreImage($name, $directory);
            $imageName = null;

            DB::table('categories')->insert([
                'business_id' => 1000,
                'name' => $name,
                'image_url' => $imageName,
                'type' => 'Products',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($serviceCategories as $name) {
            // $imageName = $this->downloadAndStoreImage($name, $directory);
            $imageName = null;

            DB::table('categories')->insert([
                'business_id' => 1000,
                'name' => $name,
                'image_url' => $imageName,
                'type' => 'Services',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Download image from picsum and store it using the logic from helpers.
     */
    private function downloadAndStoreImage($name, $directory)
    {
        $slug = Str::slug($name);
        $url = 'https://picsum.photos/seed/' . $slug . '/500/500';

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $tempPath = tempnam(sys_get_temp_dir(), 'cat_img_');
                file_put_contents($tempPath, $response->body());

                // Create an UploadedFile instance to be used with fileUploadStorage
                $file = new UploadedFile($tempPath, $slug . '.jpg', 'image/jpeg', null, true);

                // Directly use the fileUploadStorage function from helpers
                $imageName = fileUploadStorage($file, $directory, 500, 500);

                // Cleanup temp file is handled by UploadedFile if passed true to test mode, 
                // but let's be safe if it's not.
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                return $imageName;
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return "";
    }
}
