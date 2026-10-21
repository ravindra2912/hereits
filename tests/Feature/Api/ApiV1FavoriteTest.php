<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ApiV1FavoriteTest extends TestCase
{
    /**
     * Test toggle favorite endpoint requires authentication.
     */
    public function test_toggle_favorite_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/toggle-favorite', [
            'type' => 'business',
            'item_id' => 1,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status_code' => 401,
            ]);
    }

    /**
     * Test toggle favorite validates required input.
     */
    public function test_toggle_favorite_validates_required_fields(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user in database.');
        }

        Passport::actingAs($user, ['*'], 'api');

        $response = $this->postJson('/api/v1/toggle-favorite', [
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status_code' => 422,
            ]);
    }

    /**
     * Test user can follow and unfollow a business.
     */
    public function test_user_can_follow_and_unfollow_business(): void
    {
        $user = User::first();
        $business = Business::first();

        if (!$user || !$business) {
            $this->markTestSkipped('User or Business not found in database.');
        }

        Passport::actingAs($user, ['*'], 'api');

        // Clean any pre-existing favorite for clean test state
        Favorite::where('user_id', $user->id)
            ->where('favorite_type', 'business')
            ->where('favorite_item_id', $business->id)
            ->delete();

        // 1. Follow business
        $followResponse = $this->postJson('/api/v1/toggle-favorite', [
            'business_id' => $business->id,
            'type' => 'business',
            'item_id' => $business->id,
        ]);

        $followResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_favorite' => true,
                    'action' => 'added',
                    'favorite_type' => 'business',
                    'favorite_item_id' => $business->id,
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'business_id' => $business->id,
            'favorite_type' => 'business',
            'favorite_item_id' => $business->id,
        ]);

        // 2. Business detail reflects is_favorited = true for this user
        $detailResponse = $this->getJson('/api/v1/business/' . $business->id);
        $detailResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'business' => [
                        'id' => $business->id,
                        'is_favorited' => true,
                        'is_following' => true,
                    ],
                ],
            ]);

        // 3. Unfollow business
        $unfollowResponse = $this->postJson('/api/v1/toggle-favorite', [
            'business_id' => $business->id,
            'type' => 'business',
            'item_id' => $business->id,
        ]);

        $unfollowResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_favorite' => false,
                    'action' => 'removed',
                ],
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'business_id' => $business->id,
            'favorite_type' => 'business',
            'favorite_item_id' => $business->id,
        ]);
    }

    /**
     * Test user can favorite and unfavorite a product.
     */
    public function test_user_can_favorite_and_unfavorite_product(): void
    {
        $user = User::first();
        $product = Product::first();

        if (!$user || !$product) {
            $this->markTestSkipped('User or Product not found in database.');
        }

        Passport::actingAs($user, ['*'], 'api');

        // Clean pre-existing
        Favorite::where('user_id', $user->id)
            ->where('favorite_type', 'product')
            ->where('favorite_item_id', $product->id)
            ->delete();

        // 1. Add favorite product
        $favResponse = $this->postJson('/api/v1/toggle-favorite', [
            'business_id' => $product->business_id,
            'type' => 'product',
            'item_id' => $product->id,
        ]);

        $favResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_favorite' => true,
                    'action' => 'added',
                    'favorite_type' => 'product',
                    'favorite_item_id' => $product->id,
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'business_id' => $product->business_id,
            'favorite_type' => 'product',
            'favorite_item_id' => $product->id,
        ]);

        // 2. Unfavorite product
        $unfavResponse = $this->postJson('/api/v1/toggle-favorite', [
            'business_id' => $product->business_id,
            'type' => 'product',
            'item_id' => $product->id,
        ]);

        $unfavResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_favorite' => false,
                    'action' => 'removed',
                ],
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'business_id' => $product->business_id,
            'favorite_type' => 'product',
            'favorite_item_id' => $product->id,
        ]);
    }

    /**
     * Test user favorites list returns paginated favorites.
     */
    public function test_user_favorites_list_returns_paginated_data(): void
    {
        $user = User::first();
        $business = Business::first();

        if (!$user || !$business) {
            $this->markTestSkipped('User or Business not found in database.');
        }

        Passport::actingAs($user, ['*'], 'api');

        // Ensure at least one followed business
        Favorite::updateOrCreate([
            'user_id' => $user->id,
            'favorite_type' => 'business',
            'favorite_item_id' => $business->id,
        ], [
            'business_id' => $business->id,
        ]);

        $response = $this->getJson('/api/v1/user/favorites?type=business');

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
                    'favorites',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $favorites = $response->json('data.favorites');
        $this->assertIsArray($favorites);
        $this->assertNotEmpty($favorites);
        $this->assertEquals('business', $favorites[0]['favorite_type']);
        $this->assertNotNull($favorites[0]['business']);
    }
}
