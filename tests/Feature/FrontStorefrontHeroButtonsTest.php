<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FrontStorefrontHeroButtonsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hero_cta_buttons_render_shop_now_call_now_and_direction()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'Hospital & Clinic',
            'slug' => 'hospital-clinic-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Maa Hospital',
            'slug' => 'maa-hospital-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
            'template' => 'common',
            'contact' => (string) rand(6000000000, 9999999999),
            'is_contact_verify' => true,
            'address' => 'Surat, Gujarat',
            'latitude' => '21.1702',
            'longitude' => '72.8311',
        ])->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'is_ecommerce_system' => 1,
            'is_show_contact' => 1,
        ]);

        $response = $this->get(route('business-details', $business->slug));

        $response->assertStatus(200);
        $response->assertSee('hero-cta-buttons');
        $response->assertSee('hero-cta-btn-primary');
        $response->assertSee('Shop Now');
        $response->assertSee('hero-cta-btn-call');
        $response->assertSee('Call Now');
        $response->assertSee('hero-cta-btn-direction');
        $response->assertSee('Direction');
    }
}
