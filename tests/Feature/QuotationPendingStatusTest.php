<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Repositories\Eloquent\QuotationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuotationPendingStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_whatsapp_quotation_is_created_with_pending_status()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'business_id' => $business->id,
        ])->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
            'is_create_quotation_on_inquiry' => 1,
        ]);

        $product = new Product();
        $product->forceFill([
            'business_id' => $business->id,
            'name' => 'Sample Item',
            'price' => 150.00,
            'sell_price' => 150.00,
            'price_type' => 'FixPrice',
            'status' => 'active',
            'quantity' => 10,
        ])->save();

        $repo = app(QuotationRepository::class);
        $quotation = $repo->createWhatsAppQuotation($business, [
            ['id' => $product->id, 'qty' => 2, 'price' => 150.00]
        ]);

        $this->assertEquals(Quotation::STATUS_PENDING, $quotation->status);
        $this->assertEquals('pending', $quotation->status);
        $this->assertEquals(Quotation::CREATED_FROM_INQUIRY, $quotation->created_from);
        $this->assertEquals('inquiry', $quotation->created_from);
    }

    public function test_editing_quotation_transitions_status_from_pending_to_inprogress()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $product = new Product();
        $product->forceFill([
            'business_id' => $business->id,
            'name' => 'Sample Item',
            'price' => 100.00,
            'sell_price' => 100.00,
            'price_type' => 'FixPrice',
            'status' => 'active',
            'quantity' => 10,
        ])->save();

        $quotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-TEST',
            'subtotal' => 100,
            'total' => 100,
            'status' => Quotation::STATUS_PENDING,
        ]);

        $repo = app(QuotationRepository::class);
        $updated = $repo->updateQuotation($quotation->id, $business->id, [
            'discount' => 10,
            'items' => [
                ['product_id' => $product->id, 'price' => 100, 'qty' => 1]
            ]
        ]);

        $this->assertEquals(Quotation::STATUS_INPROGRESS, $updated->fresh()->status);
    }

    public function test_converting_pending_quotation_to_order()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'business_id' => $business->id,
        ])->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
        ]);

        $product = new Product();
        $product->forceFill([
            'business_id' => $business->id,
            'name' => 'Sample Item',
            'price' => 100.00,
            'sell_price' => 100.00,
            'price_type' => 'FixPrice',
            'status' => 'active',
            'quantity' => 10,
        ])->save();

        $quotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-TEST2',
            'subtotal' => 100,
            'total' => 100,
            'status' => Quotation::STATUS_PENDING,
        ]);

        \App\Models\QuotationItem::create([
            'quotation_id' => $quotation->id,
            'item_id' => $product->id,
            'item_name' => $product->name,
            'price' => 100,
            'qty' => 1,
        ]);

        $repo = app(QuotationRepository::class);
        $order = $repo->convertToOrder($quotation->id, $business->id, $owner->id);

        $this->assertEquals(Quotation::STATUS_ORDERED, $quotation->fresh()->status);
        $this->assertEquals($order->id, $quotation->fresh()->order_id);
    }

    public function test_canceling_pending_quotation()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $quotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-TEST3',
            'subtotal' => 100,
            'total' => 100,
            'status' => Quotation::STATUS_PENDING,
        ]);

        $repo = app(QuotationRepository::class);
        $repo->cancelQuotation($quotation->id, $business->id, 'Customer declined');

        $this->assertEquals(Quotation::STATUS_CANCEL, $quotation->fresh()->status);
        $this->assertEquals('Customer declined', $quotation->fresh()->reason);
    }

    public function test_quotations_are_searchable_by_customer_name_and_contact()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Owner',
            'last_name' => 'User',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);
        $business->save();

        $owner->business_id = $business->id;
        $owner->save();

        $registeredCustomer = new User();
        $registeredCustomer->forceFill([
            'first_name' => 'Amitabh',
            'last_name' => 'Bachchan',
            'email' => 'amitabh_' . uniqid() . '@example.com',
            'contact' => '9876543210',
            'password' => bcrypt('password'),
            'role' => 'User',
            'status' => 'active',
        ])->save();

        // Quotation 1: with registered customer
        Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-AMIT',
            'customer_id' => $registeredCustomer->id,
            'subtotal' => 500,
            'total' => 500,
            'status' => Quotation::STATUS_INPROGRESS,
        ]);

        // Quotation 2: walk-in with custom name and contact
        Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-RAHUL',
            'customer_name' => 'Rahul Sharma',
            'customer_contact' => '9123456780',
            'subtotal' => 300,
            'total' => 300,
            'status' => Quotation::STATUS_PENDING,
        ]);

        $this->actingAs($owner, 'pos');
        session([
            'pos_business_id' => $business->id,
            'pos_business_details' => $business,
            'pos_permissions' => ['all_access' => true],
        ]);

        // Search by customer name (registered)
        $res1 = $this->getJson(route('pos.quotation.index', [
            'search' => ['value' => 'Amitabh']
        ]), ['X-Requested-With' => 'XMLHttpRequest']);
        $res1->assertStatus(200);
        $this->assertStringContainsString('QT-' . date('Ymd') . '-AMIT', json_encode($res1->json()));
        $this->assertStringNotContainsString('QT-' . date('Ymd') . '-RAHUL', json_encode($res1->json()));

        // Search by customer contact (walk-in)
        $res2 = $this->getJson(route('pos.quotation.index', [
            'search' => ['value' => '9123456780']
        ]), ['X-Requested-With' => 'XMLHttpRequest']);
        $res2->assertStatus(200);
        $this->assertStringContainsString('QT-' . date('Ymd') . '-RAHUL', json_encode($res2->json()));
        $this->assertStringNotContainsString('QT-' . date('Ymd') . '-AMIT', json_encode($res2->json()));
    }

    public function test_auto_expire_command_expires_past_quotations()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        // Expired past quotation (pending)
        $pastQuote1 = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-EX1',
            'subtotal' => 100,
            'total' => 100,
            'status' => Quotation::STATUS_PENDING,
            'valid_until' => now()->subMinute(),
        ]);

        // Expired past quotation (inprogress)
        $pastQuote2 = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-EX2',
            'subtotal' => 200,
            'total' => 200,
            'status' => Quotation::STATUS_INPROGRESS,
            'valid_until' => now()->subHours(2),
        ]);

        // Future quotation (should NOT expire)
        $futureQuote = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-FUT',
            'subtotal' => 300,
            'total' => 300,
            'status' => Quotation::STATUS_INPROGRESS,
            'valid_until' => now()->addHours(2),
        ]);

        // Ordered quotation with past date (should NOT change to expired)
        $orderedQuote = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-ORD',
            'subtotal' => 400,
            'total' => 400,
            'status' => Quotation::STATUS_ORDERED,
            'valid_until' => now()->subHours(5),
        ]);

        $this->artisan('app:quotation-check-expired')
            ->assertExitCode(0);

        $this->assertEquals(Quotation::STATUS_EXPIRED, $pastQuote1->fresh()->status);
        $this->assertEquals(Quotation::STATUS_EXPIRED, $pastQuote2->fresh()->status);
        $this->assertEquals(Quotation::STATUS_INPROGRESS, $futureQuote->fresh()->status);
        $this->assertEquals(Quotation::STATUS_ORDERED, $orderedQuote->fresh()->status);
    }

    public function test_duplicating_quotation_creates_pending_quotation_with_null_order_id()
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => 'owner_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'business_id' => $business->id,
        ])->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
        ]);

        $product = new Product();
        $product->forceFill([
            'business_id' => $business->id,
            'name' => 'Sample Item',
            'price' => 150.00,
            'sell_price' => 150.00,
            'price_type' => 'FixPrice',
            'status' => 'active',
            'quantity' => 10,
        ]);
        $product->save();

        // Source quotation: started as pending then converted to ordered with a valid order_id
        $sourceQuotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . date('Ymd') . '-ORIG',
            'created_by_id' => $owner->id,
            'customer_name' => 'John Doe',
            'customer_contact' => '9876543210',
            'subtotal' => 300,
            'discount' => 20,
            'shipping_charge' => 50,
            'tax' => 10,
            'total' => 340,
            'status' => Quotation::STATUS_PENDING,
            'notes' => 'Original notes',
        ]);

        \App\Models\QuotationItem::create([
            'quotation_id' => $sourceQuotation->id,
            'item_id' => $product->id,
            'item_name' => $product->name,
            'price' => 150,
            'qty' => 2,
        ]);

        $repo = app(QuotationRepository::class);
        $order = $repo->convertToOrder($sourceQuotation->id, $business->id, $owner->id);
        $sourceQuotation->refresh();

        $this->assertEquals(Quotation::STATUS_ORDERED, $sourceQuotation->status);
        $this->assertEquals($order->id, $sourceQuotation->order_id);

        $duplicated = $repo->duplicateQuotation($sourceQuotation->id, $business->id, $owner->id);

        $this->assertNotNull($duplicated);
        $this->assertNotEquals($sourceQuotation->id, $duplicated->id);
        $this->assertNotEquals($sourceQuotation->quotation_no, $duplicated->quotation_no);
        $this->assertEquals(Quotation::STATUS_PENDING, $duplicated->status);
        $this->assertNull($duplicated->order_id);
        $this->assertEquals('John Doe', $duplicated->customer_name);
        $this->assertEquals('9876543210', $duplicated->customer_contact);
        $this->assertEquals(340, $duplicated->total);
        $this->assertEquals('Original notes', $duplicated->notes);

        // Check items cloned
        $this->assertCount(1, $duplicated->items);
        $this->assertEquals($product->id, $duplicated->items->first()->item_id);
        $this->assertEquals(2, $duplicated->items->first()->qty);
        $this->assertEquals(150, $duplicated->items->first()->price);
    }
}

