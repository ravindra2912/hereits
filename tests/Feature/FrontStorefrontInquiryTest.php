<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessInquiry;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FrontStorefrontInquiryTest extends TestCase
{
    use DatabaseTransactions;

    private function createBusinessSetup(array $settings = []): array
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name'   => 'Inquiry Test Category ' . uniqid(),
            'slug'   => 'inquiry-cat-' . uniqid(),
            'status' => 'active',
        ])->save();

        $owner = User::create([
            'first_name' => 'Store',
            'last_name'  => 'Owner',
            'email'      => 'owner_' . uniqid() . '@example.com',
            'contact'    => '98' . rand(10000000, 99999999),
            'password'   => bcrypt('password'),
            'role'       => 'Business',
            'status'     => 'active',
        ]);

        $business = new Business();
        $business->forceFill([
            'name'                 => 'Inquiry Test Business',
            'slug'                 => 'inquiry-biz-' . uniqid(),
            'owner_id'             => $owner->id,
            'business_type'        => 'Product',
            'business_category_id' => $category->id,
            'city_id'              => 1,
            'state_id'             => 1,
            'address'              => '123 Market Street',
            'area'                 => 'Central',
            'pincode'              => '395001',
            'contact'              => '99' . rand(10000000, 99999999),
            'status'               => 'active',
        ])->save();

        $owner->business_id = $business->id;
        $owner->save();

        $mergedSettings = array_merge([
            'business_id'                   => $business->id,
            'credit'                        => 100,
            'is_ecommerce_system'           => true,
            'is_order_place_on_website'     => true,
            'is_create_quotation_on_inquiry'=> false,
            'is_login_required'             => false,
            'is_show_contact'               => true,
        ], $settings);

        $businessSetting = BusinessSetting::create($mergedSettings);

        return [$business, $owner, $businessSetting];
    }

    /**
     * Test submitting a product inquiry as a guest creates inquiry and notifies business.
     */
    public function test_guest_can_submit_product_inquiry_and_business_is_notified(): void
    {
        [$business, $owner] = $this->createBusinessSetup();

        $product = Product::create([
            'business_id' => $business->id,
            'name'        => 'Test Gadget',
            'slug'        => 'test-gadget-' . uniqid(),
            'sell_price'  => 499.00,
            'price_type'  => 'PriceInRange',
            'min_price'   => 400.00,
            'max_price'   => 550.00,
            'quantity'    => 10,
            'status'      => 'active',
        ]);

        $response = $this->postJson(route('business.inquiry.store', $business->slug), [
            'name'         => 'Alice Wonder',
            'contact'      => '9876543210',
            'email'        => 'alice@example.com',
            'subject'      => 'Price clarification',
            'message'      => 'Is this product available for bulk order?',
            'inquiry_type' => 'product',
            'source_id'    => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $inquiryId = $response->json('inquiry_id');
        $this->assertNotNull($inquiryId);

        $this->assertDatabaseHas('business_inquiries', [
            'id'           => $inquiryId,
            'business_id'  => $business->id,
            'name'         => 'Alice Wonder',
            'contact'      => '9876543210',
            'email'        => 'alice@example.com',
            'inquiry_type' => 'product',
            'system_status'=> BusinessInquiry::SYSTEM_STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('business_inquiry_sources', [
            'inquiry_id'  => $inquiryId,
            'source_id'   => $product->id,
            'source_type' => 'product',
        ]);

        // Check notification recorded in DB for business owner
        $this->assertDatabaseHas('notifications', [
            'user_id'     => $owner->id,
            'business_id' => $business->id,
        ]);
    }

    /**
     * Test submitting a service inquiry creates inquiry with service source.
     */
    public function test_can_submit_service_inquiry(): void
    {
        [$business, $owner] = $this->createBusinessSetup();

        $category = new Category();
        $category->forceFill([
            'business_id' => $business->id,
            'name'        => 'Cleaning',
            'status'      => 'active',
            'type'        => 'Services',
        ])->save();

        $service = Service::create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name'        => 'Deep Cleaning Service',
            'slug'        => 'deep-cleaning-' . uniqid(),
            'price'       => 1500.00,
            'price_type'  => 'FixPrice',
            'status'      => 'active',
        ]);

        $response = $this->postJson(route('business.inquiry.store', $business->slug), [
            'name'         => 'Bob Builder',
            'contact'      => '9123456789',
            'email'        => 'bob@example.com',
            'message'      => 'Need service for weekend.',
            'inquiry_type' => 'service',
            'source_id'    => $service->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $inquiryId = $response->json('inquiry_id');
        $this->assertDatabaseHas('business_inquiries', [
            'id'           => $inquiryId,
            'business_id'  => $business->id,
            'inquiry_type' => 'service',
        ]);

        $this->assertDatabaseHas('business_inquiry_sources', [
            'inquiry_id'  => $inquiryId,
            'source_id'   => $service->id,
            'source_type' => 'service',
        ]);
    }

    /**
     * Test submitting a contact us inquiry from home page form.
     */
    public function test_can_submit_contact_us_inquiry(): void
    {
        [$business, $owner] = $this->createBusinessSetup();

        $response = $this->postJson(route('business.inquiry.store', $business->slug), [
            'name'         => 'Charlie Chaplin',
            'contact'      => '9898989898',
            'email'        => 'charlie@example.com',
            'subject'      => 'Store Timings',
            'message'      => 'Are you open on Sundays?',
            'inquiry_type' => 'contact_us',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('business_inquiries', [
            'business_id'  => $business->id,
            'name'         => 'Charlie Chaplin',
            'contact'      => '9898989898',
            'inquiry_type' => 'contact_us',
        ]);
    }

    /**
     * Test cart checkout inquiry with multiple items and quotation creation enabled.
     */
    /**
     * Test cart checkout inquiry creates sources without creating any quotation.
     */
    public function test_cart_inquiry_creates_sources_without_creating_quotation(): void
    {
        [$business, $owner, $setting] = $this->createBusinessSetup([
            'is_create_quotation_on_inquiry' => true,
            'credit'                         => 100,
        ]);

        $p1 = Product::create([
            'business_id' => $business->id,
            'name'        => 'Item 1',
            'slug'        => 'item-1-' . uniqid(),
            'sell_price'  => 100.00,
            'price_type'  => 'FixPrice',
            'quantity'    => 5,
            'status'      => 'active',
        ]);

        $p2 = Product::create([
            'business_id' => $business->id,
            'name'        => 'Item 2',
            'slug'        => 'item-2-' . uniqid(),
            'sell_price'  => 250.00,
            'price_type'  => 'FixPrice',
            'quantity'    => 5,
            'status'      => 'active',
        ]);

        $response = $this->postJson(route('business.inquiry.store', $business->slug), [
            'name'         => 'Diana Prince',
            'contact'      => '9777777777',
            'email'        => 'diana@example.com',
            'inquiry_type' => 'product',
            'items'        => [
                ['id' => $p1->id, 'qty' => 2, 'price' => 100.00],
                ['id' => $p2->id, 'qty' => 1, 'price' => 250.00],
            ],
            'sources'      => [
                ['source_id' => $p1->id, 'source_type' => 'product'],
                ['source_id' => $p2->id, 'source_type' => 'product'],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $inquiryId = $response->json('inquiry_id');

        // Exactly 2 unique sources are created, no duplicates
        $this->assertEquals(2, \App\Models\BusinessInquirySource::where('inquiry_id', $inquiryId)->count());

        // Both products are recorded as sources
        $this->assertDatabaseHas('business_inquiry_sources', [
            'inquiry_id' => $inquiryId,
            'source_id'  => $p1->id,
        ]);
        $this->assertDatabaseHas('business_inquiry_sources', [
            'inquiry_id' => $inquiryId,
            'source_id'  => $p2->id,
        ]);

        // Verifying NO quotation is created on inquiry
        $this->assertEquals(0, \App\Models\Quotation::where('business_id', $business->id)->count());
    }

    /**
     * Test inquiry endpoint enforces login when is_login_required is true.
     */
    public function test_inquiry_enforces_login_when_setting_is_enabled(): void
    {
        [$business, $owner, $setting] = $this->createBusinessSetup([
            'is_login_required' => true,
        ]);

        // Guest submission should return 401 requires_login
        $guestResponse = $this->postJson(route('business.inquiry.store', $business->slug), [
            'name'         => 'Guest User',
            'contact'      => '9998887776',
            'inquiry_type' => 'contact_us',
        ]);

        $guestResponse->assertStatus(401);
        $guestResponse->assertJson([
            'status'         => 'error',
            'requires_login' => true,
        ]);

        // Logged-in user should succeed
        $customer = User::create([
            'first_name'        => 'Authenticated',
            'last_name'         => 'User',
            'email'             => 'auth_user_' . uniqid() . '@example.com',
            'contact'           => '9998887776',
            'password'          => bcrypt('password'),
            'role'              => 'User',
            'is_contact_verify' => true,
        ]);

        $authResponse = $this->actingAs($customer, 'web')->postJson(route('business.inquiry.store', $business->slug), [
            'inquiry_type' => 'contact_us',
            'name'         => 'Authenticated User',
            'contact'      => '9998887776',
            'message'      => 'Hello from logged in customer',
        ]);

        $authResponse->assertStatus(200);
        $authResponse->assertJson(['status' => 'success']);
    }

    /**
     * Test validation failure returns 422 JSON with clear message.
     */
    public function test_inquiry_validation_failure(): void
    {
        [$business] = $this->createBusinessSetup();

        $response = $this->postJson(route('business.inquiry.store', $business->slug), [
            // Missing name and contact
            'inquiry_type' => 'contact_us',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
        ]);
    }
}
