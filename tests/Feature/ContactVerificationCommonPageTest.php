<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Expert;
use App\Models\OtpManagement;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContactVerificationCommonPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WhatsAppService so real HTTP calls to Meta Cloud API are not made during tests
        $mockWhatsApp = \Mockery::mock(WhatsAppService::class);
        $mockWhatsApp->shouldReceive('sendOtp')
            ->andReturn([
                'success' => true,
                'message' => 'OTP dispatched successfully.',
                'message_id' => 'wamid.test_' . uniqid(),
            ]);
        $mockWhatsApp->shouldReceive('sanitizeContact')
            ->andReturnUsing(function ($contact) {
                return substr(preg_replace('/\D+/', '', $contact), -10);
            });

        $this->app->instance(WhatsAppService::class, $mockWhatsApp);
    }

    public function test_guest_can_access_home_without_redirection()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_logged_in_user_with_unverified_contact_is_redirected_to_verify_contact()
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'testuser_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')->get('/');
        $response->assertRedirect(route('verify.contact'));
    }

    public function test_verify_contact_page_renders_with_target_metadata()
    {
        $user = User::create([
            'first_name' => 'Verify',
            'last_name' => 'Test',
            'email' => 'verifytest_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => '9876543210',
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')->get(route('verify.contact'));
        $response->assertStatus(200);
        $response->assertSee('Verify Your Contact Number');
        $response->assertSee('9876543210');
    }

    public function test_user_can_send_otp_on_verify_contact_page()
    {
        $user = User::create([
            'first_name' => 'Otp',
            'last_name' => 'Sender',
            'email' => 'otpsender_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')->postJson(route('verify.contact.send-otp'), [
            'contact' => '9876543211',
            'type' => 'user',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('otp_management', [
            'contact' => '9876543211',
            'type' => 'user',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_verify_otp_and_is_redirected_to_their_panel()
    {
        $contact = '9876543212';
        $user = User::create([
            'first_name' => 'Verif',
            'last_name' => 'Complete',
            'email' => 'verifcomplete_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        OtpManagement::create([
            'contact' => $contact,
            'otp' => '654321',
            'source' => 'whatsapp',
            'type' => 'user',
            'valid' => 10,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(route('verify.contact.verify-otp'), [
            'contact' => $contact,
            'otp' => '654321',
            'type' => 'user',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals($contact, $user->contact);
        $this->assertTrue((bool) $user->is_contact_verify);

        // Next visit to home should not be redirected
        $homeResponse = $this->actingAs($user, 'web')->get('/');
        $homeResponse->assertStatus(200);
    }

    public function test_business_owner_with_unverified_business_contact_is_redirected()
    {
        $user = User::create([
            'first_name' => 'Biz',
            'last_name' => 'Owner',
            'email' => 'bizowner_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'Business',
            'contact' => '9876543213',
            'is_contact_verify' => 1, // User contact is verified
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => 'Test Business Salon',
            'slug' => 'test-biz-' . uniqid(),
            'business_type' => 'Service',
            'contact' => null, // Business contact is missing
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $user->business_id = $business->id;
        $user->save();

        session(['current_business_id' => $business->id]);

        $response = $this->actingAs($user, 'web')->get(route('business.dashboard'));
        $response->assertRedirect(route('verify.contact'));

        // Visit verify-contact page should ask for Business contact
        $verifyPage = $this->actingAs($user, 'web')->get(route('verify.contact'));
        $verifyPage->assertStatus(200);
        $verifyPage->assertSee('Verify Business Contact');
        $verifyPage->assertSee('Test Business Salon');
    }

    public function test_pos_user_with_unverified_contact_is_redirected()
    {
        $posUser = User::create([
            'first_name' => 'POS',
            'last_name' => 'Cashier',
            'email' => 'poscashier_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($posUser, 'pos')->get(route('pos.dashboard'));
        $response->assertRedirect(route('verify.contact'));
    }

    public function test_expert_user_with_unverified_contact_is_redirected()
    {
        $expertUser = User::create([
            'first_name' => 'Dr',
            'last_name' => 'Specialist',
            'email' => 'expert_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $business = Business::create([
            'name' => 'Clinic Center',
            'slug' => 'clinic-' . uniqid(),
            'business_type' => 'Appointment',
            'contact' => '9876543214',
            'is_contact_verify' => 1,
            'status' => 'active',
        ]);

        $expert = new Expert();
        $expert->user_id = $expertUser->id;
        $expert->business_id = $business->id;
        $expert->slug = 'expert-' . uniqid();
        $expert->status = 'active';
        $expert->save();

        $response = $this->actingAs($expert, 'expert')->get(route('expert.dashboard'));
        $response->assertRedirect(route('verify.contact'));
    }

    public function test_already_verified_user_visiting_verify_contact_is_redirected_to_panel()
    {
        $user = User::create([
            'first_name' => 'Already',
            'last_name' => 'Verified',
            'email' => 'alreadyverif_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => '9876543215',
            'is_contact_verify' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')->get(route('verify.contact'));
        $response->assertRedirect(route('home'));
    }

    public function test_ajax_request_by_unverified_user_returns_json_403_with_redirect()
    {
        $user = User::create([
            'first_name' => 'Ajax',
            'last_name' => 'Unverified',
            'email' => 'ajaxunverif_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => null,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/account/orders');
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'redirect' => route('verify.contact'),
        ]);
    }

    public function test_verify_otp_fails_with_invalid_code()
    {
        $contact = '9876543216';
        $user = User::create([
            'first_name' => 'Wrong',
            'last_name' => 'Code',
            'email' => 'wrongcode_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'contact' => $contact,
            'is_contact_verify' => 0,
            'status' => 'active',
        ]);

        OtpManagement::create([
            'contact' => $contact,
            'otp' => '123456',
            'source' => 'whatsapp',
            'type' => 'user',
            'valid' => 10,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(route('verify.contact.verify-otp'), [
            'contact' => $contact,
            'otp' => '999999',
            'type' => 'user',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $user->refresh();
        $this->assertFalse((bool) $user->is_contact_verify);
    }
}
