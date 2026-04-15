<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use DB;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $businessId = getBusinessId();

        foreach ($rows as $row) {
            // Basic validation
            if (!isset($row['name']) || empty($row['name'])) {
                continue;
            }

            if (blank($row['sku'] ?? null)) {
                continue;
            }

            // Find or Create Category
            $categoryId = null;
            if (isset($row['category']) && !empty($row['category'])) {
                $category = Category::where('name', $row['category'])
                    ->where('business_id', $businessId)
                    ->where('type', 'Products')
                    ->first();

                if (!$category) {
                    $category = Category::create([
                        'business_id' => $businessId,
                        'name' => $row['category'],
                        // 'slug' => generateUniqueSlug(Category::class, $row['category'], 'slug', $businessId),
                        'type' => 'Products',
                        'status' => 'active'
                    ]);
                }
                $categoryId = $category->id;
            }

            // Find existing product by SKU if provided
            $sku = isset($row['sku']) ? $row['sku'] : null;
            $product = null;
            if ($sku) {
                $product = Product::where('sku', $sku)->where('business_id', $businessId)->first();
            }

            $productData = [
                'category_id' => $categoryId,
                'name' => $row['name'],
                'sku' => $sku,
                'description' => isset($row['description']) ? $row['description'] : null,
                'price_type' => isset($row['price_type']) ? $row['price_type'] : 'FixPrice',
                'price' => isset($row['price']) ? $row['price'] : 0,
                'sell_price' => isset($row['sell_price']) ? $row['sell_price'] : 0,
                'min_price' => isset($row['min_price']) ? $row['min_price'] : null,
                'max_price' => isset($row['max_price']) ? $row['max_price'] : null,
            ];

            $productImages = collect();
            if ($product) {
                // Update existing product - Status is NOT changed
                $product->update($productData);
                // Get current images of the product
                $productImages = ProductImage::where('product_id', $product->id)->get()->keyBy('id');
            } else {
                // Create new product - Status defaults to active
                $productData['business_id'] = $businessId;
                $productData['slug'] = generateUniqueSlug(Product::class, $row['name'], 'slug', $businessId);
                $productData['status'] = 'active';
                if (!$sku) {
                    $productData['sku'] = $productData['slug'];
                }
                $product = Product::create($productData);
            }

            // Handle Images (Image 1 to Image 5)
            for ($i = 1; $i <= 5; $i++) {
                $imgKey = 'image_' . $i;
                if (isset($row[$imgKey]) && !empty($row[$imgKey])) {
                    $imageUrl = $row[$imgKey];
                    $processedPath = null;

                    // If it's a local asset URL, extract the storage path
                    $storageAssetPath = asset('storage/');
                    if (str_contains($imageUrl, $storageAssetPath)) {
                        $processedPath = str_replace($storageAssetPath, '', $imageUrl);
                        $processedPath = ltrim($processedPath, '/');
                    } elseif (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $processedPath = $imageUrl;
                        // Download external image and use fileUploadStorage for consistency
                        // try {
                        //     $response = Http::get($imageUrl);
                        //     if ($response->successful()) {
                        //         $tempFile = tempnam(sys_get_temp_dir(), 'import_img');
                        //         file_put_contents($tempFile, $response->body());

                        //         $fileName = basename(parse_url($imageUrl, PHP_URL_PATH)) ?: 'image.jpg';

                        //         $uploadedFile = new UploadedFile(
                        //             $tempFile,
                        //             $fileName,
                        //             null,
                        //             null,
                        //             true // mark as test to allow move from temp
                        //         );

                        //         // Use the helper for resizing, webp conversion and consistent naming
                        //         $processedPath = fileUploadStorage($uploadedFile, 'product', 900, 900);

                        //         if (file_exists($tempFile)) {
                        //             @unlink($tempFile);
                        //         }
                        //     }
                        // } catch (\Exception $e) {
                        //     // Skip or log error
                        // }
                    } else {
                        // Assume it's already a relative path
                        $processedPath = ltrim($imageUrl, '/');
                    }

                    if ($processedPath) {
                        // Check if this image already belongs to the product
                        $alreadyAttached = false;
                        foreach ($productImages as $id => $pi) {
                            if ($pi->image_url == $processedPath) {
                                $productImages->forget($id);
                                $alreadyAttached = true;
                                break;
                            }
                        }

                        if (!$alreadyAttached) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_url' => $processedPath
                            ]);
                        }
                    }
                }
            }

            // Remove images that were NOT in the import list
            foreach ($productImages as $productImage) {
                $existsInOther = ProductImage::where('product_id', '!=', $productImage->product_id)
                    ->where('image_url', $productImage->image_url)
                    ->exists();

                if (!$existsInOther) {
                    fileRemoveStorage($productImage->image_url);
                }
                $productImage->delete();
            }
        }
    }
}
