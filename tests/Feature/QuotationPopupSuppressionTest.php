<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessHomePopup;
use App\Models\BusinessSetting;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuotationPopupSuppressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_popup_is_not_displayed_on_quotation_details_page()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Popup Store',
            'slug' => 'popup-store-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
            'template' => 'common',
            'address' => '123 Main Street',
            'contact' => 9999999999,
            'is_contact_verify' => true,
        ]);
        $business->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'business_id' => $business->id,
            'is_contact_verify' => true,
        ]);
        $owner->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
        ]);

        $popup = BusinessHomePopup::create([
            'business_id' => $business->id,
            'title' => 'Special Promo Popup',
            'description' => 'Huge discounts today only!',
            'popup_type' => 'content',
            'status' => 'active',
            'trigger_delay_seconds' => 1,
            'display_frequency' => 'daily',
        ]);

        $quotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-' . strtoupper(uniqid()),
            'subtotal' => 500,
            'total' => 500,
            'status' => 'pending',
            'created_from' => 'inquiry',
            'created_by' => $owner->id,
        ]);

        // 1. Check Storefront Home page displays the popup
        $homeResponse = $this->get(route('business-details', $business->slug));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Special Promo Popup');
        $homeResponse->assertSee('businessHomePopupModal_' . $popup->id);

        // 2. Check Quotation Details page does NOT display the popup
        $quotationResponse = $this->actingAs($owner)->get(route('quotation-details', [
            'business_slug' => $business->slug,
            'quotation_no' => $quotation->quotation_no,
        ]));

        $quotationResponse->assertStatus(200);
        $quotationResponse->assertDontSee('Special Promo Popup');
        $quotationResponse->assertDontSee('businessHomePopupModal_' . $popup->id);
        $quotationResponse->assertDontSee('home-popup-modal');
    }
}
