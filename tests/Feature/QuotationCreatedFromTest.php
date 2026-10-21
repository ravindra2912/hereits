<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCustomer;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Repositories\Eloquent\QuotationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuotationCreatedFromTest extends TestCase
{
    use DatabaseTransactions;

    private function setupTestData(): array
    {
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General Category',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $business = new Business();
        $business->forceFill([
            'name' => 'Test POS Business',
            'slug' => 'test-pos-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ]);
        $business->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe_' . uniqid() . '@example.com',
            'contact' => rand(1000000000, 9999999999),
            'password' => bcrypt('password'),
            'role' => 'Business',
            'status' => 'active',
            'business_id' => $business->id,
        ]);
        $owner->save();

        BusinessSetting::create([
            'business_id' => $business->id,
            'credit' => 100,
            'is_create_quotation_on_inquiry' => 1,
        ]);

        $product = new Product();
        $product->forceFill([
            'business_id' => $business->id,
            'name' => 'Sample Product',
            'price' => 200.00,
            'sell_price' => 200.00,
            'price_type' => 'FixPrice',
            'status' => 'active',
            'quantity' => 20,
        ]);
        $product->save();

        return [$business, $owner, $product];
    }

    public function test_whatsapp_inquiry_creates_quotation_with_created_from_inquiry(): void
    {
        [$business, $owner, $product] = $this->setupTestData();

        $repo = app(QuotationRepository::class);
        $quotation = $repo->createWhatsAppQuotation($business, [
            ['id' => $product->id, 'qty' => 1, 'price' => 200.00]
        ]);

        $this->assertEquals(Quotation::CREATED_FROM_INQUIRY, $quotation->created_from);
        $this->assertEquals('inquiry', $quotation->created_from);

        $fromDb = Quotation::select('id', 'created_from')->find($quotation->id);
        $this->assertEquals('inquiry', $fromDb->created_from);
    }

    public function test_pos_quotation_creation_defaults_to_pos(): void
    {
        [$business, $owner, $product] = $this->setupTestData();

        $repo = app(QuotationRepository::class);
        $quotation = $repo->createQuotation([
            'customer_contact' => '9876543210',
            'customer_name' => 'Walkin Customer',
            'items' => [
                ['product_id' => $product->id, 'price' => 200.00, 'qty' => 1]
            ]
        ], $business->id, $owner->id);

        $this->assertEquals(Quotation::CREATED_FROM_POS, $quotation->created_from);
        $this->assertEquals('pos', $quotation->created_from);

        $fromDb = Quotation::select('id', 'created_from')->find($quotation->id);
        $this->assertEquals('pos', $fromDb->created_from);
    }

    public function test_converting_quotation_to_order_places_business_customer_id(): void
    {
        [$business, $owner, $product] = $this->setupTestData();

        $businessCustomer = BusinessCustomer::create([
            'business_id' => $business->id,
            'customer_id' => $owner->id,
            'customer_name' => 'Registered Customer',
            'contact' => '9898989898',
        ]);

        $repo = app(QuotationRepository::class);
        $quotation = $repo->createQuotation([
            'business_customer_id' => $businessCustomer->id,
            'customer_contact' => '9898989898',
            'customer_name' => 'Registered Customer',
            'items' => [
                ['product_id' => $product->id, 'price' => 200.00, 'qty' => 1]
            ]
        ], $business->id, $owner->id);

        $order = $repo->convertToOrder($quotation->id, $business->id, $owner->id);

        $this->assertEquals($businessCustomer->id, $order->business_customer_id);
    }

    public function test_converting_quotation_without_business_customer_places_null(): void
    {
        [$business, $owner, $product] = $this->setupTestData();

        $repo = app(QuotationRepository::class);
        $quotation = Quotation::create([
            'business_id' => $business->id,
            'quotation_no' => 'QT-' . uniqid(),
            'created_by_id' => $owner->id,
            'business_customer_id' => null,
            'customer_name' => null,
            'customer_contact' => null,
            'subtotal' => 200,
            'total' => 200,
            'status' => Quotation::STATUS_PENDING,
        ]);
        \App\Models\QuotationItem::create([
            'quotation_id' => $quotation->id,
            'item_id' => $product->id,
            'item_name' => $product->name,
            'price' => 200,
            'qty' => 1,
        ]);

        $order = $repo->convertToOrder($quotation->id, $business->id, $owner->id);

        $this->assertNull($order->business_customer_id);
    }
}
