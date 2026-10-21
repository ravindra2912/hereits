<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Expert;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoriteTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Business $business;
    protected Product $product;
    protected Service $service;
    protected Expert $expert;

    protected function setUp(): void
    {
        parent::setUp();

        $uniq = uniqid();

        $this->user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe.' . $uniq . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'User',
        ]);

        $owner = User::create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner.' . $uniq . '@example.com',
            'contact' => substr((time() + 1) . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'Business',
        ]);

        $this->business = Business::create([
            'name' => 'Test Business ' . $uniq,
            'slug' => 'test-business-' . $uniq,
            'owner_id' => $owner->id,
            'contact' => substr((time() + 2) . rand(100, 999), -10),
            'status' => 'active',
        ]);

        BusinessSetting::create([
            'business_id' => $this->business->id,
            'is_ecommerce_system' => true,
            'is_service_system' => true,
            'is_expert_system' => true,
            'subscription_expiry_date' => now()->addYear(),
        ]);

        $cat = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'business_id' => $this->business->id,
            'type' => 'Products',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'name' => 'Sample Product',
            'slug' => 'sample-product',
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'price' => 500,
            'sell_price' => 450,
            'price_type' => 'FixPrice',
            'status' => 'active',
        ]);

        $this->service = Service::create([
            'name' => 'Sample Service',
            'slug' => 'sample-service',
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'price' => 1000,
            'price_type' => 'FixPrice',
            'status' => 'active',
        ]);

        $this->expert = new Expert();
        $this->expert->forceFill([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'slug' => 'dr-smith',
            'title' => 'Senior Specialist',
            'rating' => 4.8,
            'status' => 'active',
        ])->save();
    }

    public function test_guest_cannot_toggle_favorite()
    {
        $response = $this->postJson(route('favorite.toggle'), [
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
            'business_id' => $this->business->id,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'requires_login' => true,
        ]);
    }

    public function test_authenticated_user_can_toggle_product_favorite()
    {
        $response = $this->actingAs($this->user)->postJson(route('favorite.toggle'), [
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
            'business_id' => $this->business->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorite' => true,
            'action' => 'added',
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
        ]);

        // Toggle off
        $response2 = $this->actingAs($this->user)->postJson(route('favorite.toggle'), [
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
            'business_id' => $this->business->id,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'is_favorite' => false,
            'action' => 'removed',
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
        ]);
    }

    public function test_user_can_toggle_service_and_expert_favorites()
    {
        // Service
        $res1 = $this->actingAs($this->user)->postJson(route('favorite.toggle'), [
            'favorite_type' => 'service',
            'favorite_item_id' => $this->service->id,
            'business_id' => $this->business->id,
        ]);
        $res1->assertStatus(200)->assertJson(['success' => true, 'is_favorite' => true]);

        // Expert
        $res2 = $this->actingAs($this->user)->postJson(route('favorite.toggle'), [
            'favorite_type' => 'expert',
            'favorite_item_id' => $this->expert->id,
            'business_id' => $this->business->id,
        ]);
        $res2->assertStatus(200)->assertJson(['success' => true, 'is_favorite' => true]);

        $this->assertEquals(2, Favorite::where('user_id', $this->user->id)->count());
    }

    public function test_account_favorites_page_renders_with_items_and_filters()
    {
        Favorite::create([
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
        ]);

        Favorite::create([
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'favorite_type' => 'service',
            'favorite_item_id' => $this->service->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('account.favorites'));
        $response->assertStatus(200);
        $response->assertSee('Sample Product');
        $response->assertSee('Sample Service');

        // Filter by product
        $responseProduct = $this->actingAs($this->user)->get(route('account.favorites', ['type' => 'product']));
        $responseProduct->assertStatus(200);
        $responseProduct->assertSee('Sample Product');

        // Filter by expert (should show empty state or 0)
        $responseExpert = $this->actingAs($this->user)->get(route('account.favorites', ['type' => 'expert']));
        $responseExpert->assertStatus(200);
        $responseExpert->assertSee('No Favorites Found');
    }

    public function test_user_can_remove_favorite_via_account_endpoint()
    {
        $fav = Favorite::create([
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('account.favorites.remove'), [
            'id' => $fav->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('favorites', ['id' => $fav->id]);
    }

    public function test_is_favorite_helper_works_correctly()
    {
        Favorite::create([
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'favorite_type' => 'product',
            'favorite_item_id' => $this->product->id,
        ]);

        $this->actingAs($this->user);

        $this->assertTrue(isFavorite('product', $this->product->id));
        $this->assertFalse(isFavorite('product', 999999));
        $this->assertFalse(isFavorite('service', $this->service->id));
    }
}
