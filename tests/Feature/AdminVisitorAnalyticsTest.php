<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\BusinessCategory;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\AdminVisitorAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminVisitorAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    protected AdminVisitorAnalyticsService $service;
    protected Admin $admin;
    protected Business $business;
    protected Product $product;
    protected Service $serviceModel;
    protected Expert $expert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AdminVisitorAnalyticsService();

        // 1. Create an Admin user
        $this->admin = new Admin();
        $this->admin->forceFill([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ])->save();

        // 2. Create Business Category & Business
        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'Retail',
            'slug' => 'retail-' . uniqid(),
            'status' => 'active',
        ])->save();

        $owner = new User();
        $owner->forceFill([
            'first_name' => 'Biz',
            'last_name' => 'Owner',
            'email' => 'owner-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'Business',
        ])->save();

        $this->business = new Business();
        $this->business->forceFill([
            'name' => 'Acme Store',
            'slug' => 'acme-store-' . uniqid(),
            'business_category_id' => $category->id,
            'owner_id' => $owner->id,
            'status' => 'active',
        ])->save();

        $owner->update(['business_id' => $this->business->id]);

        // 3. Create Product
        $this->product = new Product();
        $this->product->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Premium Shirt',
            'slug' => 'premium-shirt-' . uniqid(),
            'price' => 999.00,
            'sell_price' => 799.00,
            'status' => 'active',
        ])->save();

        // 4. Create Service
        $this->serviceModel = new Service();
        $this->serviceModel->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Consulting Call',
            'slug' => 'consulting-call-' . uniqid(),
            'price' => 1500.00,
            'status' => 'active',
        ])->save();

        // 5. Create Expert
        $this->expert = new Expert();
        $this->expert->forceFill([
            'business_id' => $this->business->id,
            'user_id' => $owner->id,
            'slug' => 'dr-smith-' . uniqid(),
            'title' => 'Senior Advisor',
            'status' => 'active',
        ])->save();
    }

    /**
     * Unauthenticated guest is redirected to admin login.
     */
    public function test_guest_cannot_access_admin_visitors(): void
    {
        $response = $this->get(route('admin.visitors.index'));
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Authenticated admin can view the visitor analytics page.
     */
    public function test_admin_can_access_visitor_analytics_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.visitors.index');
        $response->assertViewHas([
            'filter',
            'dateRangeLabel',
            'chartTitle',
            'totalViews',
            'uniqueVisitors',
            'businessViews',
            'productViews',
            'serviceViews',
            'expertViews',
            'trendLabels',
            'trendTotalViews',
            'trendUniqueVisitors',
            'entityBreakdown',
            'topBusinesses',
            'topProducts',
            'topServices',
            'topExperts',
        ]);
    }

    /**
     * Test analytics aggregation for business, product, service, and expert visits.
     */
    public function test_admin_visitor_analytics_service_aggregates_all_entities(): void
    {
        $now = Carbon::now();

        // Seed visit events for today
        BusinessAnalyticsEvent::create([
            'business_id' => $this->business->id,
            'visitor_hash' => 'hash_test_1',
            'event' => 'view',
            'page_type' => 'business',
            'page_id' => $this->business->id,
            'device' => 'desktop',
            'browser' => 'Chrome',
            'created_at' => $now,
        ]);

        BusinessAnalyticsEvent::create([
            'business_id' => $this->business->id,
            'visitor_hash' => 'hash_test_2',
            'event' => 'view',
            'page_type' => 'product',
            'page_id' => $this->product->id,
            'device' => 'mobile',
            'browser' => 'Safari',
            'created_at' => $now,
        ]);

        BusinessAnalyticsEvent::create([
            'business_id' => $this->business->id,
            'visitor_hash' => 'hash_test_1',
            'event' => 'view',
            'page_type' => 'service',
            'page_id' => $this->serviceModel->id,
            'device' => 'desktop',
            'browser' => 'Chrome',
            'created_at' => $now,
        ]);

        BusinessAnalyticsEvent::create([
            'business_id' => $this->business->id,
            'visitor_hash' => 'hash_test_3',
            'event' => 'view',
            'page_type' => 'expert',
            'page_id' => $this->expert->id,
            'device' => 'tablet',
            'browser' => 'Firefox',
            'created_at' => $now,
        ]);

        $analytics = $this->service->getAnalytics('today');

        $this->assertGreaterThanOrEqual(4, $analytics['totalViews']);
        $this->assertGreaterThanOrEqual(3, $analytics['uniqueVisitors']);
        $this->assertGreaterThanOrEqual(1, $analytics['businessViews']);
        $this->assertGreaterThanOrEqual(1, $analytics['productViews']);
        $this->assertGreaterThanOrEqual(1, $analytics['serviceViews']);
        $this->assertGreaterThanOrEqual(1, $analytics['expertViews']);

        // Check top lists have entries
        $this->assertTrue($analytics['topBusinesses']->contains('business_id', $this->business->id));
        $this->assertTrue($analytics['topProducts']->contains('product_id', $this->product->id));
        $this->assertTrue($analytics['topServices']->contains('service_id', $this->serviceModel->id));
        $this->assertTrue($analytics['topExperts']->contains('expert_id', $this->expert->id));
    }

    /**
     * Test date filters: week, this_month, last_month, custom.
     */
    public function test_admin_visitor_analytics_date_filters(): void
    {
        // 1. Week filter
        $responseWeek = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index', ['filter' => 'week']));
        $responseWeek->assertStatus(200);
        $responseWeek->assertViewHas('filter', 'week');

        // 2. This Month filter
        $responseMonth = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index', ['filter' => 'this_month']));
        $responseMonth->assertStatus(200);
        $responseMonth->assertViewHas('filter', 'this_month');

        // 3. Last Month filter
        $responseLastMonth = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index', ['filter' => 'last_month']));
        $responseLastMonth->assertStatus(200);
        $responseLastMonth->assertViewHas('filter', 'last_month');

        // 4. All Time filter
        $responseAll = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index', ['filter' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertViewHas('filter', 'all');

        // 5. Custom filter
        $fromDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $toDate = Carbon::now()->format('Y-m-d');
        $responseCustom = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.visitors.index', [
                'filter' => 'custom',
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]));
        $responseCustom->assertStatus(200);
        $responseCustom->assertViewHas('filter', 'custom');
    }
}

