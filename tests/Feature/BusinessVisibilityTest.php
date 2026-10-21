<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BusinessVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that the register business view renders visibility options with Public as default.
     */
    public function test_register_business_view_renders_public_as_default_visibility(): void
    {
        $user = \App\Models\User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'vis_test_' . uniqid() . '@example.com',
            'contact' => substr(time() . rand(100, 999), -10),
            'password' => bcrypt('password'),
            'role' => 'User',
        ]);

        $response = $this->actingAs($user)->get(route('register.business'));

        $response->assertStatus(200);
        $response->assertSee('Business Visibility');
        $response->assertSee('id="visibility_public"', false);
        $response->assertSee('id="visibility_private"', false);
        $response->assertSee('value="0" checked', false);
    }

    /**
     * Test that scopePublic only includes public businesses.
     */
    public function test_scope_public_excludes_private_businesses(): void
    {
        $publicBusiness = Business::create([
            'name' => 'Public Test Business',
            'slug' => 'public-test-business-' . uniqid(),
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

        BusinessSetting::create([
            'business_id' => $publicBusiness->id,
            'is_private' => false,
        ]);

        $privateBusiness = Business::create([
            'name' => 'Private Test Business',
            'slug' => 'private-test-business-' . uniqid(),
            'business_type' => 'Product',
            'business_category_id' => 1,
            'city_id' => 1,
            'state_id' => 1,
            'address' => 'Test Address',
            'area' => 'Test Area',
            'pincode' => '123456',
            'contact' => '88888' . rand(10000, 99999),
            'status' => 'active',
        ]);

        BusinessSetting::create([
            'business_id' => $privateBusiness->id,
            'is_private' => true,
        ]);

        $publicBusinessIds = Business::public()->pluck('id')->toArray();

        $this->assertContains($publicBusiness->id, $publicBusinessIds);
        $this->assertNotContains($privateBusiness->id, $publicBusinessIds);
    }

    /**
     * Test BusinessSetting default and object formatting.
     */
    public function test_business_setting_defaults_to_public(): void
    {
        $business = Business::create([
            'name' => 'Default Setting Business',
            'slug' => 'default-setting-business-' . uniqid(),
            'business_type' => 'Product',
            'business_category_id' => 1,
            'city_id' => 1,
            'state_id' => 1,
            'address' => 'Test Address',
            'area' => 'Test Area',
            'pincode' => '123456',
            'contact' => '77777' . rand(10000, 99999),
            'status' => 'active',
        ]);

        $setting = BusinessSetting::create([
            'business_id' => $business->id,
        ]);

        $this->assertFalse((bool)$setting->is_private);
        $this->assertFalse((bool)$setting->is_privet);

        $obj = $setting->getBusinessSettingObject();
        $this->assertFalse($obj['is_private']);
    }

    /**
     * Test admin create business view has Visibility field defaulting to Public.
     */
    public function test_admin_create_business_view_contains_visibility_field(): void
    {
        $admin = \App\Models\Admin::first() ?? \App\Models\Admin::create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.business.create'));
        $response->assertStatus(200);
        $response->assertSee('Visibility');
        $response->assertSee('<option value="0" selected>Public</option>', false);
        $response->assertSee('<option value="1">Private</option>', false);
    }
}
