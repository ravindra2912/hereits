<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class ProductAndCategoryApiTest extends TestCase
{
    /**
     * Test business products endpoint returns only fields used by mobile app.
     */
    public function test_api_v1_business_products_returns_required_fields_only(): void
    {
        $business = Business::first();
        if (!$business) {
            $this->markTestSkipped('No business exists in database.');
        }

        $response = $this->getJson('/api/v1/business/' . $business->id . '/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'products',
                ],
            ]);

        $responseData = $response->json('data.products');
        if (!empty($responseData)) {
            $firstProduct = $responseData[0];
            $this->assertArrayHasKey('id', $firstProduct);
            $this->assertArrayHasKey('name', $firstProduct);
            $this->assertArrayHasKey('price_type', $firstProduct);
            $this->assertArrayHasKey('price', $firstProduct);
            $this->assertArrayHasKey('sell_price', $firstProduct);
            $this->assertArrayHasKey('min_price', $firstProduct);
            $this->assertArrayHasKey('max_price', $firstProduct);

            // Ensure internal model fields are not exposed
            $this->assertArrayNotHasKey('sku', $firstProduct);
            $this->assertArrayNotHasKey('length', $firstProduct);
            $this->assertArrayNotHasKey('width', $firstProduct);
            $this->assertArrayNotHasKey('height', $firstProduct);
            $this->assertArrayNotHasKey('weight', $firstProduct);
            $this->assertArrayNotHasKey('deleted_at', $firstProduct);
        }
    }

    /**
     * Test business product detail returns product, business and related products.
     */
    public function test_api_v1_product_detail_returns_expected_structure(): void
    {
        $product = Product::where('status', 'active')->first();
        if (!$product) {
            $this->markTestSkipped('No active product exists in database.');
        }

        $response = $this->getJson('/api/v1/business/product/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'product' => [
                        'id',
                        'name',
                        'price_type',
                        'images',
                    ],
                    'business' => [
                        'id',
                        'name',
                    ],
                    'relatedProducts',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals($product->id, $data['product']['id']);
        $this->assertArrayNotHasKey('sku', $data['product']);
    }

    /**
     * Test business categories endpoint returns structured categories.
     */
    public function test_api_v1_business_categories_returns_clean_structure(): void
    {
        $business = Business::first();
        if (!$business) {
            $this->markTestSkipped('No business exists in database.');
        }

        $response = $this->getJson('/api/v1/business/' . $business->id . '/categories?type=Products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'permissions' => [
                        'has_products',
                        'has_services',
                    ],
                    'categories',
                ],
            ]);

        $this->assertEquals(14, $response->json('data.per_page'));
        $this->assertGreaterThanOrEqual(0, $response->json('data.total'));

        $categories = $response->json('data.categories');
        $this->assertLessThanOrEqual(14, count($categories));

        if (!empty($categories)) {
            $firstCategory = $categories[0];
            $this->assertArrayHasKey('id', $firstCategory);
            $this->assertArrayHasKey('name', $firstCategory);
            $this->assertArrayHasKey('slug', $firstCategory);
            $this->assertArrayHasKey('type', $firstCategory);
            $this->assertArrayHasKey('image_url', $firstCategory);
            // Ensure internal model fields are not exposed
            $this->assertArrayNotHasKey('created_at', $firstCategory);
            $this->assertArrayNotHasKey('updated_at', $firstCategory);
        }
    }
}
