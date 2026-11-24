<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Product;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use App\Services\CreditService;
use App\Services\WhatsAppOtpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class BusinessMultiCategoryAndItemCategoryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Country $country;
    protected State $state;
    protected City $city;
    protected BusinessCategory $cat1;
    protected BusinessCategory $cat2;
    protected BusinessCategory $cat3;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->country = new Country();
        $this->country->forceFill([
            'sortname' => 'IN',
            'name' => 'India',
            'phonecode' => 91,
        ])->save();

        $this->state = new State();
        $this->state->forceFill([
            'name' => 'Gujarat',
            'country_id' => $this->country->id,
        ])->save();

        $this->city = new City();
        $this->city->forceFill([
            'name' => 'Surat',
            'state_id' => $this->state->id,
        ])->save();

        $this->cat1 = new BusinessCategory();
        $this->cat1->forceFill([
            'name' => 'Retail ' . uniqid(),
            'slug' => 'retail-' . uniqid(),
            'status' => 'active',
            'charge_per_contact_us_inquiry' => 1.0,
            'charge_per_product_inquiry' => 1.0,
            'charge_per_service_inquiry' => 1.0,
        ])->save();

        $this->cat2 = new BusinessCategory();
        $this->cat2->forceFill([
            'name' => 'Electronics ' . uniqid(),
            'slug' => 'electronics-' . uniqid(),
            'status' => 'active',
            'charge_per_contact_us_inquiry' => 1.5,
            'charge_per_product_inquiry' => 2.0,
            'charge_per_service_inquiry' => 2.5,
        ])->save();

        $this->cat3 = new BusinessCategory();
        $this->cat3->forceFill([
            'name' => 'Healthcare ' . uniqid(),
            'slug' => 'healthcare-' . uniqid(),
            'status' => 'active',
            'charge_per_contact_us_inquiry' => 1.0,
            'charge_per_product_inquiry' => 1.0,
            'charge_per_service_inquiry' => 1.0,
        ])->save();

        $this->user = new User();
        $this->user->forceFill([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner-' . uniqid() . '@example.com',
            'contact' => '9888888888',
            'is_contact_verify' => true,
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 1,
        ])->save();
    }

    public function test_business_registration_with_multiple_categories(): void
    {
        $this->actingAs($this->user);

        // Mock WhatsApp OTP service to simulate verified contact
        $mockOtp = Mockery::mock(WhatsAppOtpService::class);
        $mockOtp->shouldReceive('isContactVerified')->andReturn(true);
        $mockOtp->shouldReceive('consumeVerification')->andReturn(true);
        $this->app->instance(WhatsAppOtpService::class, $mockOtp);

        $image = UploadedFile::fake()->image('store.jpg', 800, 600);
        $logo = UploadedFile::fake()->image('logo.jpg', 200, 200);

        $contact = '9876' . rand(100000, 999999);

        $response = $this->post(route('register.business.store'), [
            'business_name' => 'Mega Mart ' . uniqid(),
            'business_contact' => $contact,
            'whatsapp_verification_token' => 'test-token',
            'business_category_ids' => [$this->cat1->id, $this->cat2->id],
            'business_type' => 'Product',
            'address' => 'Ring Road, Surat',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Majura',
            'pincode' => '395002',
            'latitude' => 21.1702,
            'longitude' => 72.8311,
            'business_image' => $image,
            'business_logo' => $logo,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $business = Business::where('contact', $contact)->first();
        $this->assertNotNull($business);
        $this->assertEquals($this->cat1->id, $business->business_category_id); // Primary category

        // Check pivot table
        $mappedCategoryIds = $business->businessCategories->pluck('id')->toArray();
        $this->assertCount(2, $mappedCategoryIds);
        $this->assertContains($this->cat1->id, $mappedCategoryIds);
        $this->assertContains($this->cat2->id, $mappedCategoryIds);
    }

    public function test_business_profile_update_categories_tab(): void
    {
        $business = new Business();
        $business->forceFill([
            'owner_id' => $this->user->id,
            'business_category_id' => $this->cat1->id,
            'name' => 'Biz Profile Test',
            'slug' => 'biz-profile-' . uniqid(),
            'contact' => '9777777777',
            'address' => 'Station Road',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Varachha',
            'pincode' => '395006',
            'latitude' => 21.2000,
            'longitude' => 72.8500,
            'status' => 'active',
            'is_contact_verify' => true,
        ])->save();

        $business->businessCategories()->sync([$this->cat1->id]);

        BusinessSetting::updateOrCreate(
            ['business_id' => $business->id],
            ['credit' => 20.00]
        );

        $this->user->business_id = $business->id;
        $this->user->save();
        $this->user->syncBusinessSessionData($business->id);

        $this->actingAs($this->user);
        session(['business_id' => $business->id]);

        // 1. Check profile view contains all categories and store categories tab
        $viewRes = $this->get(route('business.setting.business'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Store Categories');
        $viewRes->assertSee($this->cat1->name);
        $viewRes->assertSee($this->cat2->name);

        // 2. Update business categories
        $updateRes = $this->post(route('business.setting.business.update', $business->id), [
            'name' => 'Updated Biz Mart',
            'contact' => '9777777777',
            'business_category_ids' => [$this->cat2->id, $this->cat3->id],
            'address' => 'Station Road Updated',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Varachha',
            'pincode' => '395006',
            'latitude' => 21.2000,
            'longitude' => 72.8500,
        ]);

        $updateRes->assertStatus(200);
        $this->assertTrue($updateRes->json('success'));

        $business->refresh();
        $this->assertEquals($this->cat2->id, $business->business_category_id);
        $updatedMapped = $business->businessCategories->pluck('id')->toArray();
        $this->assertCount(2, $updatedMapped);
        $this->assertContains($this->cat2->id, $updatedMapped);
        $this->assertContains($this->cat3->id, $updatedMapped);
        $this->assertNotContains($this->cat1->id, $updatedMapped);
    }

    public function test_product_store_category_is_mandatory(): void
    {
        $business = new Business();
        $business->forceFill([
            'owner_id' => $this->user->id,
            'business_category_id' => $this->cat1->id,
            'name' => 'Product Store Test',
            'slug' => 'prod-store-' . uniqid(),
            'contact' => '9666666661',
            'address' => 'City Light',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Citylight',
            'pincode' => '395007',
            'status' => 'active',
            'is_contact_verify' => true,
        ])->save();

        $business->businessCategories()->sync([$this->cat1->id, $this->cat2->id]);

        BusinessSetting::updateOrCreate(
            ['business_id' => $business->id],
            [
                'is_ecommerce_system' => true,
                'credit' => 20.00,
            ]
        );

        $this->user->business_id = $business->id;
        $this->user->save();
        $this->user->syncBusinessSessionData($business->id);

        $this->actingAs($this->user);
        session(['business_id' => $business->id]);

        // Create internal product category
        $internalCategory = new Category();
        $internalCategory->forceFill([
            'business_id' => $business->id,
            'name' => 'Shoes',
            'slug' => 'shoes-' . uniqid(),
            'type' => 'Products',
            'status' => 'active',
        ])->save();

        // 1. Missing business_category_id should fail validation
        $failRes = $this->postJson(route('business.product.store'), [
            'name' => 'Running Shoes',
            'sku' => 'SKU-' . uniqid(),
            'category_id' => $internalCategory->id,
            'quantity' => 10,
        ]);

        $failRes->assertStatus(422);
        $failRes->assertJsonValidationErrors('business_category_id');

        // 2. Providing valid business_category_id should succeed
        $passRes = $this->postJson(route('business.product.store'), [
            'name' => 'Running Shoes',
            'sku' => 'SKU-' . uniqid(),
            'business_category_id' => $this->cat2->id,
            'category_id' => $internalCategory->id,
            'quantity' => 10,
        ]);

        $passRes->assertStatus(200);
        $this->assertTrue($passRes->json('success'));

        $product = Product::latest('id')->first();
        $this->assertEquals($this->cat2->id, $product->business_category_id);
        $this->assertEquals($internalCategory->id, $product->category_id);

        // 3. Update product with new business_category_id
        $updateRes = $this->patchJson(route('business.product.update', $product->id), [
            'name' => 'Running Shoes Pro',
            'sku' => $product->sku,
            'business_category_id' => $this->cat1->id,
            'category_id' => $internalCategory->id,
            'description' => 'Running shoes for athletes',
            'price_type' => 'FixPrice',
            'price' => 1000,
            'sell_price' => 800,
            'status' => 'active',
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'weight' => 1,
        ]);

        $updateRes->assertStatus(200);
        $this->assertTrue($updateRes->json('success'));

        $product->refresh();
        $this->assertEquals($this->cat1->id, $product->business_category_id);
    }

    public function test_service_store_category_is_mandatory(): void
    {
        $business = new Business();
        $business->forceFill([
            'owner_id' => $this->user->id,
            'business_category_id' => $this->cat2->id,
            'name' => 'Service Store Test',
            'slug' => 'serv-store-' . uniqid(),
            'contact' => '9666666662',
            'address' => 'Adajan',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Adajan',
            'pincode' => '395009',
            'status' => 'active',
            'is_contact_verify' => true,
        ])->save();

        $business->businessCategories()->sync([$this->cat2->id, $this->cat3->id]);

        BusinessSetting::updateOrCreate(
            ['business_id' => $business->id],
            [
                'is_service_system' => true,
                'credit' => 20.00,
            ]
        );

        $this->user->business_id = $business->id;
        $this->user->save();
        $this->user->syncBusinessSessionData($business->id);

        $this->actingAs($this->user);
        session(['business_id' => $business->id]);

        // Create internal service category
        $internalCategory = new Category();
        $internalCategory->forceFill([
            'business_id' => $business->id,
            'name' => 'Consulting',
            'slug' => 'consulting-' . uniqid(),
            'type' => 'Services',
            'status' => 'active',
        ])->save();

        $image = UploadedFile::fake()->image('service.jpg', 600, 600);

        // 1. Missing business_category_id should fail validation
        $failRes = $this->postJson(route('business.service.store'), [
            'name' => 'Health Consultation',
            'category_id' => $internalCategory->id,
            'image' => $image,
            'price_type' => 'WithoutPrice',
            'status' => 'active',
        ]);

        $failRes->assertStatus(200);
        $this->assertFalse($failRes->json('success'));
        $this->assertArrayHasKey('business_category_id', $failRes->json('message'));

        // 2. Providing valid business_category_id should succeed
        $passRes = $this->post(route('business.service.store'), [
            'name' => 'Health Consultation',
            'business_category_id' => $this->cat3->id,
            'category_id' => $internalCategory->id,
            'image' => $image,
            'price_type' => 'WithoutPrice',
            'status' => 'active',
        ]);

        $passRes->assertStatus(200);
        $this->assertTrue($passRes->json('success'));

        $service = Service::latest('id')->first();
        $this->assertEquals($this->cat3->id, $service->business_category_id);
        $this->assertEquals($internalCategory->id, $service->category_id);

        // 3. Update service with new store category
        $updateRes = $this->patch(route('business.service.update', $service->id), [
            'name' => 'Health Consultation Deluxe',
            'business_category_id' => $this->cat2->id,
            'category_id' => $internalCategory->id,
            'price_type' => 'WithoutPrice',
            'status' => 'active',
        ]);

        $updateRes->assertStatus(200);
        $this->assertTrue($updateRes->json('success'));

        $service->refresh();
        $this->assertEquals($this->cat2->id, $service->business_category_id);
    }

    public function test_business_categories_modal_update_and_primary_category_setting(): void
    {
        $business = new Business();
        $business->forceFill([
            'owner_id' => $this->user->id,
            'business_category_id' => $this->cat1->id,
            'name' => 'Category Modal Biz',
            'slug' => 'cat-modal-' . uniqid(),
            'contact' => '9666666666',
            'address' => 'Ring Road',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Majura',
            'pincode' => '395001',
            'latitude' => 21.1800,
            'longitude' => 72.8200,
            'status' => 'active',
            'is_contact_verify' => true,
        ])->save();

        $business->businessCategories()->sync([$this->cat1->id]);

        $this->user->business_id = $business->id;
        $this->user->save();
        $this->user->syncBusinessSessionData($business->id);

        $this->actingAs($this->user);
        session(['business_id' => $business->id]);

        // 1. Check profile view shows Add Category button and modal
        $viewRes = $this->get(route('business.setting.business'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('manageStoreCategoriesModal');
        $viewRes->assertSee('Add Category');
        $viewRes->assertSee('Primary Category');

        // 2. Test AJAX categories update: add cat2 and cat3, make cat3 primary
        $ajaxRes = $this->post(route('business.setting.business.categories.update'), [
            'business_category_ids' => [$this->cat1->id, $this->cat2->id, $this->cat3->id],
            'primary_category_id' => $this->cat3->id,
        ]);

        $ajaxRes->assertStatus(200);
        $this->assertTrue($ajaxRes->json('success'));
        $this->assertEquals($this->cat3->id, $ajaxRes->json('data.primary_category_id'));

        $business->refresh();
        $this->assertEquals($this->cat3->id, $business->business_category_id);
        $mappedIds = $business->businessCategories->pluck('id')->toArray();
        $this->assertCount(3, $mappedIds);
        $this->assertContains($this->cat1->id, $mappedIds);
        $this->assertContains($this->cat2->id, $mappedIds);
        $this->assertContains($this->cat3->id, $mappedIds);

        // 3. Validation: missing primary category
        $invalidRes = $this->post(route('business.setting.business.categories.update'), [
            'business_category_ids' => [$this->cat1->id],
        ]);
        $invalidRes->assertStatus(422);

        // 4. Test full profile update with explicit primary_category_id
        $profileRes = $this->post(route('business.setting.business.update', $business->id), [
            'name' => 'Category Modal Biz Updated',
            'contact' => '9666666666',
            'business_category_ids' => [$this->cat1->id, $this->cat2->id],
            'primary_category_id' => $this->cat2->id,
            'address' => 'Ring Road Updated',
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'area' => 'Majura',
            'pincode' => '395001',
        ]);
        $profileRes->assertStatus(200);
        $this->assertTrue($profileRes->json('success'));

        $business->refresh();
        $this->assertEquals($this->cat2->id, $business->business_category_id);
    }
}

