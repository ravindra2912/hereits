<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessInquiry;
use App\Models\BusinessInquiryLog;
use App\Models\BusinessInquirySource;
use App\Models\BusinessSetting;
use App\Models\CreditHistory;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\BusinessInquiryService;
use App\Services\CreditService;
use DomainException;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BusinessInquiryManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected BusinessInquiryService $inquiryService;
    protected CreditService $creditService;
    protected Business $business;
    protected BusinessCategory $category;
    protected User $owner;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creditService = new CreditService();
        $this->inquiryService = new BusinessInquiryService($this->creditService);

        $this->category = new BusinessCategory();
        $this->category->forceFill([
            'name' => 'Retail',
            'slug' => 'retail-' . uniqid(),
            'status' => 'active',
            'charge_per_contact_us_inquiry' => 1.50,
            'charge_per_product_inquiry'    => 2.00,
            'charge_per_service_inquiry'    => 3.00,
        ])->save();

        $this->business = new Business();
        $this->business->forceFill([
            'name' => 'Demo Business',
            'slug' => 'demo-business-' . uniqid(),
            'business_category_id' => $this->category->id,
            'status' => 'active',
            'contact' => '9876543210',
            'is_contact_verify' => true,
        ])->save();

        $this->owner = new User();
        $this->owner->forceFill([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'owner_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'contact' => '9876543210',
            'is_contact_verify' => true,
            'role' => 'Business',
            'status' => 1,
            'business_id' => $this->business->id,
        ])->save();

        $this->business->owner_id = $this->owner->id;
        $this->business->save();

        $this->staff = new User();
        $this->staff->forceFill([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'staff_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'User',
            'status' => 1,
            'business_id' => $this->business->id,
        ])->save();

        // Business settings with initial credit
        BusinessSetting::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'credit' => 20.00,
                'is_inquiry_charge_manual' => false,
                'charge_per_contact_us_inquiry' => 0.50,
                'charge_per_product_inquiry'    => 0.75,
                'charge_per_service_inquiry'    => 1.25,
            ]
        );
    }

    public function test_new_inquiry_must_have_pending_status_and_masked_info_when_not_accepted(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id'  => $this->business->id,
            'name'         => 'Robert Johnson',
            'contact'      => '9123456789',
            'email'        => 'robert@example.com',
            'message'      => 'I am interested in your pricing.',
            'inquiry_type' => BusinessInquiry::TYPE_CONTACT_US,
        ]);

        $this->assertEquals(BusinessInquiry::SYSTEM_STATUS_PENDING, $inquiry->system_status);
        $this->assertEquals(BusinessInquiry::BUSINESS_STATUS_NEW, $inquiry->business_status);
        $this->assertFalse((bool)$inquiry->credit_deducted);
        $this->assertNull($inquiry->credit_deducted_id);
        $this->assertNull($inquiry->accepted_at);

        // Privacy rule verification
        $this->assertFalse($inquiry->isAccepted());
        $this->assertNotEquals('Robert Johnson', $inquiry->masked_name);
        $this->assertStringContainsString('*', $inquiry->masked_name);
        $this->assertNotEquals('9123456789', $inquiry->masked_contact);
        $this->assertStringContainsString('****', $inquiry->masked_contact);
        $this->assertStringContainsString('***', $inquiry->masked_email);

        // Verification of initial log
        $this->assertDatabaseHas('business_inquiry_logs', [
            'inquiry_id' => $inquiry->id,
            'action'     => 'created',
            'new_value'  => 'pending',
        ]);
    }

    public function test_accepting_inquiry_deducts_credit_and_unmasks_customer_info(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id'  => $this->business->id,
            'name'         => 'Jane Customer',
            'contact'      => '9988776655',
            'email'        => 'jane@example.com',
            'message'      => 'Need a quote for service.',
            'inquiry_type' => BusinessInquiry::TYPE_SERVICE,
        ]);

        $initialBalance = $this->creditService->getAvailableCredit($this->business->id);
        $charge = $this->creditService->getInquiryCharge($this->business->id, $inquiry->inquiry_type);
        $this->assertEquals(3.00, $charge); // 3.00 set on category for service inquiry

        $acceptedInquiry = $this->inquiryService->acceptInquiry($inquiry, 'Accepted by owner', $this->owner->id);

        $this->assertEquals(BusinessInquiry::SYSTEM_STATUS_ACCEPTED, $acceptedInquiry->system_status);
        $this->assertEquals(BusinessInquiry::BUSINESS_STATUS_NEW, $acceptedInquiry->business_status);
        $this->assertTrue((bool)$acceptedInquiry->credit_deducted);
        $this->assertNotNull($acceptedInquiry->credit_deducted_id);
        $this->assertNotNull($acceptedInquiry->accepted_at);

        // Credit check
        $newBalance = $this->creditService->getAvailableCredit($this->business->id);
        $this->assertEquals($initialBalance - $charge, $newBalance);

        // Unmasked privacy verification
        $this->assertTrue($acceptedInquiry->isAccepted());
        $this->assertEquals('Jane Customer', $acceptedInquiry->masked_name);
        $this->assertEquals('9988776655', $acceptedInquiry->masked_contact);
        $this->assertEquals('jane@example.com', $acceptedInquiry->masked_email);

        // Logs verification
        $this->assertDatabaseHas('business_inquiry_logs', [
            'inquiry_id' => $inquiry->id,
            'action'     => 'accepted',
            'old_value'  => 'pending',
            'new_value'  => 'accepted',
        ]);
    }

    public function test_inquiry_charge_resolution_from_category_and_business_settings(): void
    {
        // 1. When manual charge is disabled: uses BusinessCategory values
        BusinessSetting::where('business_id', $this->business->id)->update([
            'is_inquiry_charge_manual' => false,
        ]);

        $this->assertEquals(1.50, $this->creditService->getInquiryCharge($this->business->id, 'contact_us'));
        $this->assertEquals(2.00, $this->creditService->getInquiryCharge($this->business->id, 'product'));
        $this->assertEquals(3.00, $this->creditService->getInquiryCharge($this->business->id, 'service'));

        // 2. When manual charge is enabled: uses BusinessSetting values
        BusinessSetting::where('business_id', $this->business->id)->update([
            'is_inquiry_charge_manual' => true,
            'charge_per_contact_us_inquiry' => 0.50,
            'charge_per_product_inquiry'    => 0.75,
            'charge_per_service_inquiry'    => 1.25,
        ]);

        $this->assertEquals(0.50, $this->creditService->getInquiryCharge($this->business->id, 'contact_us'));
        $this->assertEquals(0.75, $this->creditService->getInquiryCharge($this->business->id, 'product'));
        $this->assertEquals(1.25, $this->creditService->getInquiryCharge($this->business->id, 'service'));

        // 3. Fallback when business has no category and not manual
        $businessNoCat = new Business();
        $businessNoCat->forceFill([
            'name' => 'No Cat Business',
            'slug' => 'no-cat-' . uniqid(),
            'status' => 'active',
        ])->save();

        $this->assertEquals(1.00, $this->creditService->getInquiryCharge($businessNoCat->id, 'product'));
    }

    public function test_accepted_inquiry_cannot_be_accepted_again(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Test User',
            'contact'     => '9111111111',
        ]);

        $this->inquiryService->acceptInquiry($inquiry, null, $this->owner->id);

        $this->expectException(DomainException::class);
        $this->inquiryService->acceptInquiry($inquiry->fresh(), null, $this->owner->id);
    }

    public function test_insufficient_credits_prevents_inquiry_acceptance(): void
    {
        // Drain credit balance
        BusinessSetting::where('business_id', $this->business->id)->update(['credit' => 0.00]);

        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Test User',
            'contact'     => '9111111111',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient credit balance/');

        $this->inquiryService->acceptInquiry($inquiry, null, $this->owner->id);
    }

    public function test_rejecting_inquiry_does_not_deduct_credit(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Reject Me',
            'contact'     => '9222222222',
        ]);

        $initialBalance = $this->creditService->getAvailableCredit($this->business->id);

        $rejected = $this->inquiryService->rejectInquiry($inquiry, 'Not serving this region', $this->owner->id);

        $this->assertEquals(BusinessInquiry::SYSTEM_STATUS_REJECTED, $rejected->system_status);
        $this->assertFalse((bool)$rejected->credit_deducted);
        $this->assertNull($rejected->credit_deducted_id);

        // Credit balance untouched
        $this->assertEquals($initialBalance, $this->creditService->getAvailableCredit($this->business->id));

        // Rejected inquiries cannot be accepted
        $this->expectException(DomainException::class);
        $this->inquiryService->acceptInquiry($rejected->fresh(), null, $this->owner->id);
    }

    public function test_not_fulfilled_does_not_deduct_credit(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Unfulfillable',
            'contact'     => '9333333333',
        ]);

        $initialBalance = $this->creditService->getAvailableCredit($this->business->id);

        $notFulfilled = $this->inquiryService->markNotFulfilled($inquiry, 'Cannot supply required quantity', $this->owner->id);

        $this->assertEquals(BusinessInquiry::SYSTEM_STATUS_NOT_FULFILLED, $notFulfilled->system_status);
        $this->assertFalse((bool)$notFulfilled->credit_deducted);
        $this->assertEquals($initialBalance, $this->creditService->getAvailableCredit($this->business->id));
    }

    public function test_business_sales_status_transitions_and_rules(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Sales Lead',
            'contact'     => '9444444444',
        ]);

        // Non-accepted inquiry cannot enter business workflow
        try {
            $this->inquiryService->updateBusinessStatus($inquiry, BusinessInquiry::BUSINESS_STATUS_CONTACTED);
            $this->fail('Should not allow sales status update on pending inquiry');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Only accepted inquiries', $e->getMessage());
        }

        // Accept inquiry
        $accepted = $this->inquiryService->acceptInquiry($inquiry, null, $this->owner->id);
        $balanceAfterAccept = $this->creditService->getAvailableCredit($this->business->id);

        // 1. Transition to Contacted
        $contacted = $this->inquiryService->updateBusinessStatus($accepted, BusinessInquiry::BUSINESS_STATUS_CONTACTED, [
            'remarks' => 'Called customer, positive response',
        ], $this->owner->id);
        $this->assertEquals(BusinessInquiry::BUSINESS_STATUS_CONTACTED, $contacted->business_status);
        $this->assertNotNull($contacted->contacted_at);

        // 2. Transition to Follow Up with date
        $followUpDate = now()->addDays(2)->format('Y-m-d H:i:s');
        $followUp = $this->inquiryService->updateBusinessStatus($contacted, BusinessInquiry::BUSINESS_STATUS_FOLLOW_UP, [
            'next_follow_up_at' => $followUpDate,
            'remarks'           => 'Requested quote review next Thursday',
        ], $this->owner->id);
        $this->assertEquals(BusinessInquiry::BUSINESS_STATUS_FOLLOW_UP, $followUp->business_status);
        $this->assertNotNull($followUp->next_follow_up_at);

        // 3. Transition to Converted
        $converted = $this->inquiryService->updateBusinessStatus($followUp, BusinessInquiry::BUSINESS_STATUS_CONVERTED, [
            'remarks' => 'Order placed successfully',
        ], $this->owner->id);
        $this->assertEquals(BusinessInquiry::BUSINESS_STATUS_CONVERTED, $converted->business_status);
        $this->assertNotNull($converted->converted_at);

        // RULE: Business status changes must NEVER trigger another credit deduction!
        $this->assertEquals($balanceAfterAccept, $this->creditService->getAvailableCredit($this->business->id));
    }

    public function test_staff_assignment_and_notes(): void
    {
        $inquiry = $this->inquiryService->createInquiry([
            'business_id' => $this->business->id,
            'name'        => 'Assignment Lead',
            'contact'     => '9555555555',
        ]);

        // Assign staff
        $assigned = $this->inquiryService->assignEmployee($inquiry, $this->staff->id, 'Assigning to Alice', $this->owner->id);
        $this->assertEquals($this->staff->id, $assigned->assigned_to);

        $this->assertDatabaseHas('business_inquiry_logs', [
            'inquiry_id' => $inquiry->id,
            'action'     => 'assigned',
            'new_value'  => $this->staff->name,
        ]);

        // Add note
        $note = $this->inquiryService->addNote($assigned, 'Customer called back to confirm meeting', $this->owner->id);
        $this->assertEquals('note_added', $note->action);
        $this->assertEquals('Customer called back to confirm meeting', $note->remarks);
    }

    public function test_controller_endpoints(): void
    {
        $this->actingAs($this->owner);

        $inquiry = $this->inquiryService->createInquiry([
            'business_id'  => $this->business->id,
            'name'         => 'API Tester',
            'contact'      => '9666666666',
            'email'        => 'tester@example.com',
            'message'      => 'Web controller test',
            'inquiry_type' => BusinessInquiry::TYPE_PRODUCT,
        ]);

        // 1. Index view renders
        $response = $this->get(route('business.inquiries.index'));
        $response->assertStatus(200);
        $response->assertSee('Inquiry Management');

        // 2. Show endpoint masks details when pending
        $showRes = $this->getJson(route('business.inquiries.show', $inquiry->id));
        $showRes->assertStatus(200);
        $this->assertFalse($showRes->json('data.is_accepted'));
        $this->assertStringContainsString('*', $showRes->json('data.contact'));

        // 3. Accept endpoint
        $acceptRes = $this->postJson(route('business.inquiries.accept', $inquiry->id), [
            'remarks' => 'Accepting via controller test',
        ]);
        $acceptRes->assertStatus(200);
        $this->assertEquals('success', $acceptRes->json('status'));
        $this->assertEquals('9666666666', $acceptRes->json('data.contact')); // Unlocked

        // 4. Update sales status endpoint
        $statusRes = $this->postJson(route('business.inquiries.business-status', $inquiry->id), [
            'business_status' => 'contacted',
            'remarks'         => 'Customer contacted via WhatsApp',
        ]);
        $statusRes->assertStatus(200);
        $this->assertEquals('contacted', $statusRes->json('data.business_status'));

        // 5. Assign endpoint
        $assignRes = $this->postJson(route('business.inquiries.assign', $inquiry->id), [
            'assigned_to' => $this->staff->id,
            'remarks'     => 'Delegating to Alice',
        ]);
        $assignRes->assertStatus(200);

        // 6. Add note endpoint
        $noteRes = $this->postJson(route('business.inquiries.notes.add', $inquiry->id), [
            'remarks' => 'Internal follow-up note',
        ]);
        $noteRes->assertStatus(200);

        // 7. Get logs endpoint
        $logsRes = $this->getJson(route('business.inquiries.logs', $inquiry->id));
        $logsRes->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($logsRes->json('data')));
    }

    public function test_admin_can_create_and_update_category_inquiry_charges(): void
    {
        $admin = new Admin();
        $admin->forceFill([
            'first_name' => 'Admin',
            'last_name'  => 'Test',
            'email'      => 'admin-inquiry-' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'admin',
        ])->save();

        $this->actingAs($admin, 'admin');

        // 1. Create category with custom inquiry charges
        $createRes = $this->post(route('admin.businesscategory.store'), [
            'name' => 'Health & Fitness ' . uniqid(),
            'deduct_credit_per_customer_appointment' => 1.00,
            'deduct_credit_per_self_appointment'     => 1.00,
            'charge_per_contact_us_inquiry'          => 2.50,
            'charge_per_product_inquiry'             => 3.75,
            'charge_per_service_inquiry'             => 4.25,
        ]);

        $createRes->assertStatus(200);
        $this->assertTrue($createRes->json('success'));

        $createdCategory = BusinessCategory::latest('id')->first();
        $this->assertEquals(2.50, (float) $createdCategory->charge_per_contact_us_inquiry);
        $this->assertEquals(3.75, (float) $createdCategory->charge_per_product_inquiry);
        $this->assertEquals(4.25, (float) $createdCategory->charge_per_service_inquiry);

        // 2. Update category inquiry charges
        $updateRes = $this->patch(route('admin.businesscategory.update', $createdCategory->id), [
            'name' => $createdCategory->name,
            'status' => 'active',
            'deduct_credit_per_customer_appointment' => 2.00,
            'deduct_credit_per_self_appointment'     => 2.00,
            'charge_per_contact_us_inquiry'          => 5.00,
            'charge_per_product_inquiry'             => 6.50,
            'charge_per_service_inquiry'             => 7.25,
        ]);

        $updateRes->assertStatus(200);
        $this->assertTrue($updateRes->json('success'));

        $createdCategory->refresh();
        $this->assertEquals(5.00, (float) $createdCategory->charge_per_contact_us_inquiry);
        $this->assertEquals(6.50, (float) $createdCategory->charge_per_product_inquiry);
        $this->assertEquals(7.25, (float) $createdCategory->charge_per_service_inquiry);

        // 3. DataTable index includes inquiry charges column
        $indexRes = $this->get(route('admin.businesscategory.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $indexRes->assertStatus(200);
        $this->assertStringContainsString('Contact: 5.00', $indexRes->getContent());
        $this->assertStringContainsString('Product: 6.50', $indexRes->getContent());
        $this->assertStringContainsString('Service: 7.25', $indexRes->getContent());
    }
}
