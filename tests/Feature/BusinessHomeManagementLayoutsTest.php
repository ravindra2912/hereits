<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\HomeSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BusinessHomeManagementLayoutsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_business_user_can_view_layouts_page_with_add_modal_and_local_sortable_asset()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Layout',
            'last_name' => 'Owner',
            'email' => 'layout_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'is_contact_verify' => true,
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Layout Business',
            'slug' => 'layout-business-' . uniqid(),
            'business_category_id' => $category->id,
            'owner_id' => $owner->id,
            'status' => 'active',
            'contact' => '9999999999',
            'is_contact_verify' => true,
        ])->save();

        $owner->business_id = $business->id;
        $owner->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
        ]);

        $response = $this->actingAs($owner)->get(route('business.home-management.layouts'));

        $response->assertStatus(200);
        $response->assertSee('id="openAddModalBtn"', false);
        $response->assertSee('id="addSectionModal"', false);
        $response->assertSee('assets/common/js/sortable.min.js', false);
        $response->assertDontSee('https://cdn.jsdelivr.net/npm/sortablejs', false);

        // Verify Content-Security-Policy allows cdn.jsdelivr.net as fallback
        $cspHeader = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($cspHeader);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $cspHeader);
    }
}

