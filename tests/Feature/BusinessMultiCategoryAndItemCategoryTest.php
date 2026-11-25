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

    public function test_category_requires_business_category_and_product_resolves_it(): void
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

        // 1. Creating a main product category without business_category_id should fail validation
        $catFailRes = $this->postJson(route('business.product-category.store'), [
            'name' => 'Footwear',
            'status' => 'active',
        ]);
        $catFailRes->assertStatus(422);
        $catFailRes->assertJsonValidationErrors('business_category_id');

        // 2. Creating a main product category with business_category_id succeeds
        $catPassRes = $this->postJson(route('business.product-category.store'), [
            'name' => 'Footwear',
            'business_category_id' => $this->cat2->id,
            'status' => 'active',
        ]);
        $catPassRes->assertStatus(200);
        $this->assertTrue($catPassRes->json('success'));

        $mainCategory = Category::where('name', 'Footwear')->where('business_id', $business->id)->first();
        $this->assertNotNull($mainCategory);
        $this->assertEquals($this->cat2->id, $mainCategory->business_category_id);

        // 3. Creating a subcategory inherits business_category_id from parent
        $subCatRes = $this->postJson(route('business.product-category.store'), [
            'name' => 'Running Shoes',
            'parent_id' => $mainCategory->id,
            'status' => 'active',
        ]);
        $subCatRes->assertStatus(200);
        $this->assertTrue($subCatRes->json('success'));

        $subCategory = Category::where('name', 'Running Shoes')->where('business_id', $business->id)->first();
        $this->assertNotNull($subCategory);
        $this->assertEquals($this->cat2->id, $subCategory->business_category_id);

        // 3b. Verify DataTable returns Business Category badge with category name
        $dtRes = $this->getJson(route('business.product-category.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtRes->assertStatus(200);
        $dtData = $dtRes->json('data');
        $this->assertNotEmpty($dtData);
        $footwearRow = collect($dtData)->firstWhere('name', '<a href="' . route('business.product-category.index', ['category' => $mainCategory->id]) . '" class="text-primary text-decoration-none fw-bold">Footwear</a>');
        $this->assertNotNull($footwearRow);
        $this->assertStringContainsString($this->cat2->name, $footwearRow['business_category_name']);

        // 4. Product can be created with category_id (no business_category_id needed on product)
        $prodPassRes = $this->postJson(route('business.product.store'), [
            'name' => 'Pro Runner X',
            'sku' => 'SKU-' . uniqid(),
            'category_id' => $mainCategory->id,
            'quantity' => 10,
        ]);

        $prodPassRes->assertStatus(200);
        $this->assertTrue($prodPassRes->json('success'));

        $product = Product::latest('id')->first();
        $this->assertEquals($mainCategory->id, $product->category_id);
        // Verify businessCategory relation resolves through Category
        $this->assertNotNull($product->businessCategory);
        $this->assertEquals($this->cat2->id, $product->businessCategory->id);

        // 5. Update product
        $updateRes = $this->patchJson(route('business.product.update', $product->id), [
            'name' => 'Pro Runner X Updated',
            'sku' => $product->sku,
            'category_id' => $subCategory->id,
            'description' => 'Updated running shoes',
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
        $this->assertEquals($subCategory->id, $product->category_id);
        $this->assertEquals($this->cat2->id, $product->businessCategory->id);
    }

    public function test_service_category_requires_business_category_and_service_resolves_it(): void
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

        // 1. Creating a main service category without business_category_id should fail
        $catFailRes = $this->postJson(route('business.service-category.store'), [
            'name' => 'Therapy',
            'status' => 'active',
        ]);
        $catFailRes->assertStatus(422);
        $catFailRes->assertJsonValidationErrors('business_category_id');

        // 2. Creating a main service category with business_category_id succeeds
        $catPassRes = $this->postJson(route('business.service-category.store'), [
            'name' => 'Therapy',
            'business_category_id' => $this->cat3->id,
            'status' => 'active',
        ]);
        $catPassRes->assertStatus(200);
        $this->assertTrue($catPassRes->json('success'));

        $serviceCategory = Category::where('name', 'Therapy')->where('business_id', $business->id)->first();
        $this->assertNotNull($serviceCategory);
        $this->assertEquals($this->cat3->id, $serviceCategory->business_category_id);

        $image = UploadedFile::fake()->image('service.jpg', 600, 600);

        // 3. Service can be created without business_category_id on request
        $passRes = $this->post(route('business.service.store'), [
            'name' => 'Health Consultation',
            'category_id' => $serviceCategory->id,
            'image' => $image,
            'price_type' => 'WithoutPrice',
            'status' => 'active',
        ]);

        $passRes->assertStatus(200);
        $this->assertTrue($passRes->json('success'));

        $service = Service::latest('id')->first();
        $this->assertEquals($serviceCategory->id, $service->category_id);
        // Verify businessCategory resolves through Category
        $this->assertNotNull($service->businessCategory);
        $this->assertEquals($this->cat3->id, $service->businessCategory->id);

        // 4. Update service
        $updateRes = $this->patch(route('business.service.update', $service->id), [
            'name' => 'Health Consultation Deluxe',
            'category_id' => $serviceCategory->id,
            'price_type' => 'WithoutPrice',
            'status' => 'active',
        ]);

        $updateRes->assertStatus(200);
        $this->assertTrue($updateRes->json('success'));

        $service->refresh();
        $this->assertEquals('Health Consultation Deluxe', $service->name);
        $this->assertEquals($this->cat3->id, $service->businessCategory->id);
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

