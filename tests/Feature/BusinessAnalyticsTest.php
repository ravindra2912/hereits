<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\BusinessAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class BusinessAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    protected BusinessAnalyticsService $service;
    protected Business $business;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BusinessAnalyticsService();

        $category = new BusinessCategory();
        $category->forceFill([
            'name' => 'General',
            'slug' => 'general-' . uniqid(),
            'status' => 'active',
        ])->save();

        $this->business = new Business();
        $this->business->forceFill([
            'name' => 'Test Business',
            'slug' => 'test-business-' . uniqid(),
            'business_category_id' => $category->id,
            'status' => 'active',
        ])->save();

        $this->user = new User();
        $this->user->forceFill([
            'first_name' => 'Owner',
            'last_name' => 'User',
            'email' => 'owner-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'business_id' => $this->business->id,
            'role' => 'Business',
        ])->save();

        $setting = new BusinessSetting();
        $setting->forceFill([
            'business_id' => $this->business->id,
            'is_ecommerce_system' => 1,
            'is_service_system' => 1,
            'is_appointment_system' => 1,
        ])->save();
    }

    public function test_business_view_creates_analytics_event(): void
    {
        $event = $this->service->trackView($this->business, 'business', $this->business->id);

        $this->assertNotNull($event);
        $this->assertDatabaseHas('business_analytics_events', [
            'id' => $event->id,
            'business_id' => $this->business->id,
            'page_type' => 'business',
            'page_id' => $this->business->id,
            'event' => 'view',
        ]);
    }

    public function test_product_view_creates_analytics_event(): void
    {
        $cat = new Category();
        $cat->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'type' => 'Products',
            'status' => 'active',
        ])->save();

        $product = new Product();
        $product->forceFill([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Smartphone',
            'slug' => 'smartphone-' . uniqid(),
            'price' => 500,
            'status' => 'active',
        ])->save();

        $event = $this->service->trackView($product->business_id, 'product', $product->id);

        $this->assertNotNull($event);
        $this->assertEquals('product', $event->page_type);
        $this->assertEquals($product->id, $event->page_id);
        $this->assertEquals($this->business->id, $event->business_id);
        $this->assertEquals('view', $event->event);
    }

    public function test_service_view_creates_analytics_event(): void
    {
        $serviceModel = new Service();
        $serviceModel->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Haircut',
            'slug' => 'haircut-' . uniqid(),
            'price' => 30,
            'status' => 'active',
        ])->save();

        $event = $this->service->trackView($serviceModel->business_id, 'service', $serviceModel->id);

        $this->assertNotNull($event);
        $this->assertEquals('service', $event->page_type);
        $this->assertEquals($serviceModel->id, $event->page_id);
        $this->assertEquals($this->business->id, $event->business_id);
        $this->assertEquals('view', $event->event);
    }

    public function test_expert_view_creates_analytics_event(): void
    {
        $expert = new Expert();
        $expert->forceFill([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'title' => 'Consultant',
            'slug' => 'dr-john-' . uniqid(),
            'status' => 1,
        ])->save();

        $event = $this->service->trackView($expert->business_id, 'expert', $expert->id);

        $this->assertNotNull($event);
        $this->assertEquals('expert', $event->page_type);
        $this->assertEquals($expert->id, $event->page_id);
        $this->assertEquals($this->business->id, $event->business_id);
        $this->assertEquals('view', $event->event);
    }

    public function test_guest_user_stores_null_user_id(): void
    {
        $event = $this->service->trackView($this->business, 'business', $this->business->id);

        $this->assertNull($event->user_id);
    }

    public function test_authenticated_user_stores_correct_user_id(): void
    {
        $user = new User();
        $user->forceFill([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ])->save();

        $this->actingAs($user);

        $event = $this->service->trackView($this->business, 'business', $this->business->id);

        $this->assertEquals($user->id, $event->user_id);
    }

    public function test_duplicate_immediate_view_is_prevented(): void
    {
        $request = Request::create('/' . $this->business->slug, 'GET');
        $event1 = $this->service->trackView($this->business, 'business', $this->business->id, $request);
        $this->assertNotNull($event1);

        // Immediate second view from same visitor should be prevented
        $event2 = $this->service->trackView($this->business, 'business', $this->business->id, $request);
        $this->assertNull($event2);

        $this->assertEquals(1, BusinessAnalyticsEvent::where('business_id', $this->business->id)->count());
    }

    public function test_different_page_creates_new_event(): void
    {
        $event1 = $this->service->trackView($this->business, 'business', $this->business->id);
        $this->assertNotNull($event1);

        $cat = new Category();
        $cat->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'type' => 'Products',
            'status' => 'active',
        ])->save();

        $product = new Product();
        $product->forceFill([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Laptop',
            'slug' => 'laptop-' . uniqid(),
            'price' => 1000,
            'status' => 'active',
        ])->save();

        $event2 = $this->service->trackView($this->business, 'product', $product->id);
        $this->assertNotNull($event2);

        $this->assertEquals(2, BusinessAnalyticsEvent::where('business_id', $this->business->id)->count());
    }

    public function test_utm_parameters_are_captured(): void
    {
        $request = Request::create(
            '/' . $this->business->slug . '?utm_source=google&utm_medium=cpc&utm_campaign=summer_sale',
            'GET',
            ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'summer_sale']
        );

        $event = $this->service->trackView($this->business, 'business', $this->business->id, $request);

        $this->assertEquals('google', $event->utm_source);
        $this->assertEquals('cpc', $event->utm_medium);
        $this->assertEquals('summer_sale', $event->utm_campaign);
    }

    public function test_referer_ip_and_device_information_are_captured(): void
    {
        $request = Request::create(
            '/' . $this->business->slug,
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '192.168.1.100',
                'HTTP_REFERER' => 'https://google.com',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            ]
        );

        $event = $this->service->trackView($this->business, 'business', $this->business->id, $request);

        $this->assertEquals('192.168.1.100', $event->ip_address);
        $this->assertEquals('https://google.com', $event->referer);
        $this->assertEquals('mobile', $event->device);
        $this->assertEquals('iOS', $event->os);
    }

    public function test_visitor_analytics_service_compiles_data(): void
    {
        $this->service->trackView($this->business, 'business', $this->business->id);

        $analytics = $this->service->getVisitorAnalytics($this->business->id);

        $this->assertArrayHasKey('totalVisitors', $analytics);
        $this->assertArrayHasKey('uniqueVisitors', $analytics);
        $this->assertArrayHasKey('todayVisitors', $analytics);
        $this->assertArrayHasKey('last7DaysVisitors', $analytics);
        $this->assertArrayHasKey('last30DaysVisitors', $analytics);
        $this->assertArrayHasKey('returningVisitors', $analytics);
        $this->assertArrayHasKey('dailyLabels', $analytics);
        $this->assertArrayHasKey('monthlyLabels', $analytics);
        $this->assertArrayHasKey('referralData', $analytics);
        $this->assertArrayHasKey('deviceData', $analytics);
        $this->assertArrayHasKey('browserData', $analytics);
        $this->assertArrayHasKey('topProducts', $analytics);
        $this->assertArrayHasKey('topServices', $analytics);
        $this->assertArrayHasKey('topPages', $analytics);

        $this->assertEquals(1, $analytics['totalVisitors']);
    }

    public function test_visitor_page_route_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('business.visitors'));

        $response->assertStatus(200);
        $response->assertViewIs('business.visitors.index');
        $response->assertViewHasAll([
            'totalVisitors',
            'uniqueVisitors',
            'todayVisitors',
            'last7DaysVisitors',
            'last30DaysVisitors',
            'returningVisitors',
            'dailyLabels',
            'monthlyLabels',
            'referralData',
            'deviceData',
            'browserData',
            'topProducts',
            'topServices',
            'topPages',
        ]);
    }

    public function test_owner_visit_is_not_tracked(): void
    {
        $this->actingAs($this->user);

        $event = $this->service->trackView($this->business, 'business', $this->business->id);

        $this->assertNull($event);
    }

    public function test_staff_visit_is_not_tracked(): void
    {
        $staffUser = new User();
        $staffUser->forceFill([
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'business_id' => $this->business->id,
            'role' => 'User',
        ])->save();

        $role = new \App\Models\Role();
        $role->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Staff Role',
            'permissions' => ['business_access' => true],
        ])->save();

        \App\Models\BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $staffUser->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->actingAs($staffUser);

        $event = $this->service->trackView($this->business, 'business', $this->business->id);

        $this->assertNull($event);
    }

    public function test_top_viewed_products_returns_up_to_10_products(): void
    {
        $cat = new Category();
        $cat->forceFill([
            'business_id' => $this->business->id,
            'name' => 'Top Category',
            'slug' => 'top-category-' . uniqid(),
            'type' => 'Products',
            'status' => 'active',
        ])->save();

        for ($i = 1; $i <= 12; $i++) {
            $product = new Product();
            $product->forceFill([
                'business_id' => $this->business->id,
                'category_id' => $cat->id,
                'name' => "Product {$i}",
                'slug' => "product-{$i}-" . uniqid(),
                'price' => 100 * $i,
                'status' => 'active',
            ])->save();

            BusinessAnalyticsEvent::create([
                'business_id' => $this->business->id,
                'user_id' => null,
                'session_id' => uniqid(),
                'visitor_hash' => hash('sha256', "visitor_{$i}"),
                'event' => 'view',
                'page_type' => 'product',
                'page_id' => $product->id,
                'created_at' => now(),
            ]);
        }

        $analytics = $this->service->getVisitorAnalytics($this->business->id);

        $this->assertCount(10, $analytics['topProducts']);
    }

    public function test_services_query_is_skipped_when_service_system_is_disabled(): void
    {
        BusinessSetting::where('business_id', $this->business->id)->update([
            'is_service_system' => 0,
        ]);

        $analytics = $this->service->getVisitorAnalytics($this->business->id);

        $this->assertCount(0, $analytics['topServices']);
    }

    public function test_visitor_analytics_filters_work(): void
    {
        foreach (['today', 'daily', 'monthly', 'yearly', 'custom'] as $filter) {
            $analytics = $this->service->getVisitorAnalytics(
                $this->business->id,
                $filter,
                now()->subDays(5)->format('Y-m-d'),
                now()->format('Y-m-d')
            );

            $this->assertArrayHasKey('trendLabels', $analytics);
            $this->assertArrayHasKey('trendTotalViews', $analytics);
            $this->assertArrayHasKey('trendUniqueVisitors', $analytics);
            $this->assertNotEmpty($analytics['trendLabels']);

            $response = $this->actingAs($this->user)->get(route('business.visitors', ['filter' => $filter]));
            $response->assertStatus(200);
            $response->assertViewHas('filter', $filter);
        }
    }
}


