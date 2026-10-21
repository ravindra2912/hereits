<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WhatsAppInquiryLoginSettingTest extends TestCase
{
    use DatabaseTransactions;

    private function createBusinessWithOwner(): array
    {
        $owner = User::create([
            'first_name' => 'Owner',
            'last_name' => 'Test',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'Business',
        ]);

        $business = Business::create([
            'name' => 'WhatsApp Test Business',
            'slug' => 'whatsapp-test-business-' . uniqid(),
            'owner_id' => $owner->id,
            'business_type' => 'Product',
            'business_category_id' => 1,
            'city_id' => 1,
            'state_id' => 1,
            'address' => 'Test Address',
            'area' => 'Test Area',
            'pincode' => '123456',
            'contact' => '99999' . rand(10000, 99999),
            'status' => 'active',
        ]);

        $owner->business_id = $business->id;
        $owner->save();

        $setting = BusinessSetting::create([
            'business_id' => $business->id,
            'is_ecommerce_system' => true,
            'is_order_place_on_website' => true,
            'is_create_quotation_on_inquiry' => true,
            'is_login_required' => false,
            'accept_cash_on_delivery' => true,
        ]);

        return [$owner, $business, $setting];
    }

    /**
     * Test model attribute, alias and helper serialization.
     */
    public function test_business_setting_model_casts_is_login_required_correctly(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $this->assertFalse($setting->is_login_required);

        $setting->is_login_required = true;
        $setting->save();

        $setting->refresh();
        $this->assertTrue($setting->is_login_required);

        $helperSetting = getBusinessSettings($business->id, true);
        $this->assertTrue($helperSetting->is_login_required);
    }

    /**
     * Test business configuration view renders the is_login_required switch.
     */
    public function test_business_configuration_view_renders_is_login_required(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $response = $this->actingAs($owner)->get(route('business.setting.business.configuration'));

        $response->assertStatus(200);
        $response->assertSee('name="is_login_required"', false);
        $response->assertSee('Login Required');
    }

    /**
     * Test business panel systemSettingUpdate updates is_login_required.
     */
    public function test_business_panel_system_setting_update_toggles_is_login_required(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        // 1. Enable is_login_required
        $response = $this->actingAs($owner)->postJson(route('business.setting.systemsetting.update'), [
            'form_type' => 'system_setting',
            'is_ecommerce_system' => '1',
            'accept_cash_on_delivery' => '1',
            'is_login_required' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $setting->refresh();
        $this->assertTrue((bool)$setting->is_login_required);

        // 2. Disable is_login_required
        $response = $this->actingAs($owner)->postJson(route('business.setting.systemsetting.update'), [
            'form_type' => 'system_setting',
            'is_ecommerce_system' => '1',
            'accept_cash_on_delivery' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $setting->refresh();
        $this->assertFalse((bool)$setting->is_login_required);
    }

    /**
     * Test admin business edit view renders and updates is_login_required.
     */
    public function test_admin_business_edit_renders_and_updates_is_login_required(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $admin = Admin::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.business.edit', $business->id));
        $response->assertStatus(200);
        $response->assertSee('name="is_login_required"', false);

        // Update via Admin
        $updateResponse = $this->actingAs($admin, 'admin')->postJson(route('admin.business.systemsetting.update', $business->id), [
            'is_ecommerce_system' => '1',
            'accept_cash_on_delivery' => '1',
            'is_login_required' => '1',
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        $setting->refresh();
        $this->assertTrue((bool)$setting->is_login_required);
    }

    /**
     * Test that storefront meta tag renders is-login-required.
     */
    public function test_storefront_renders_is_login_required_meta_tag(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $setting->is_login_required = true;
        $setting->save();
        getBusinessSettings($business->id, true);

        $response = $this->get(route('business-details', $business->slug));
        $response->assertStatus(200);
        $response->assertSee('<meta name="is-login-required" content="1">', false);
    }

    /**
     * Test whatsapp quotation endpoint enforces login when is_login_required is enabled.
     */
    public function test_whatsapp_quotation_enforces_login_when_setting_is_enabled(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $setting->is_login_required = true;
        $setting->is_create_quotation_on_inquiry = true;
        $setting->save();
        getBusinessSettings($business->id, true);

        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sell_price' => 100,
            'price_type' => 'FixPrice',
            'quantity' => 10,
            'status' => 'active',
        ]);

        // 1. As guest - Should be blocked with 401 and requires_login: true
        $guestResponse = $this->postJson("/{$business->slug}/whatsapp-quotation", [
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 100],
            ],
        ]);

        $guestResponse->assertStatus(401);
        $guestResponse->assertJson([
            'status' => 'error',
            'requires_login' => true,
        ]);

        // 2. As logged in user - Should succeed
        $customer = User::create([
            'first_name' => 'Customer',
            'last_name' => 'Test',
            'email' => 'customer_' . uniqid() . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'User',
        ]);

        $authResponse = $this->actingAs($customer)->postJson("/{$business->slug}/whatsapp-quotation", [
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 100],
            ],
        ]);

        $authResponse->assertStatus(200);
        $authResponse->assertJson([
            'status' => 'success',
        ]);
    }

    /**
     * Test checkout page requires login when is_login_required is enabled.
     */
    public function test_checkout_page_enforces_login_when_setting_is_enabled(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $setting->is_login_required = true;
        $setting->save();
        getBusinessSettings($business->id, true);

        $product = Product::create([
            'business_id' => $business->id,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sell_price' => 100,
            'price_type' => 'FixPrice',
            'quantity' => 10,
            'status' => 'active',
        ]);

        $cartCookieName = 'brandbatao_cart_' . $business->slug;
        $cartData = json_encode([
            ['id' => $product->id, 'name' => $product->name, 'price' => 100, 'qty' => 1, 'priceType' => 'FixPrice']
        ]);
        $_COOKIE[$cartCookieName] = $cartData;

        // 1. Guest request should redirect to business details with login_required=1
        $guestResponse = $this->withCookies([$cartCookieName => $cartData])
            ->get(route('checkout', $business->slug));

        $guestResponse->assertStatus(302);
        $guestResponse->assertRedirect(route('business-details', ['business_slug' => $business->slug, 'login_required' => 1]));

        // 2. Logged-in user should access checkout successfully
        $customer = User::create([
            'first_name' => 'Customer',
            'last_name' => 'Test',
            'email' => 'customer_' . uniqid() . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'User',
        ]);

        $authResponse = $this->actingAs($customer)
            ->withCookies([$cartCookieName => $cartData])
            ->get(route('checkout', $business->slug));

        unset($_COOKIE[$cartCookieName]);

        $authResponse->assertStatus(200);
    }

    /**
     * Test place order endpoint enforces login when is_login_required is enabled.
     */
    public function test_place_order_enforces_login_when_setting_is_enabled(): void
    {
        [$owner, $business, $setting] = $this->createBusinessWithOwner();

        $setting->is_login_required = true;
        $setting->save();
        getBusinessSettings($business->id, true);

        // Guest AJAX place order request
        $guestResponse = $this->postJson(route('place-order', $business->slug), [
            'customer_name' => 'Guest User',
            'customer_contact' => '9876543210',
            'state' => 1,
            'city' => 'City',
            'pincode' => '123456',
            'address' => 'Test Address',
            'payment_method' => 'cash',
        ]);

        $guestResponse->assertStatus(401);
        $guestResponse->assertJson([
            'status' => 'error',
            'requires_login' => true,
        ]);
    }
}
