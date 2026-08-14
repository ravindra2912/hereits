<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\BusinessAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BusinessAnalyticsServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected BusinessAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BusinessAnalyticsService::class);
        config(['analytics.duplicate_window' => 300]);
        Cache::flush();
        BusinessAnalyticsEvent::truncate();
    }

    /**
     * 1 & 5 & 6 & 7 & 8: Business view creates analytics event with correct fields.
     */
    public function test_business_view_creates_analytics_event()
    {
        $business = Business::first() ?? Business::factory()->create();

        $event = $this->service->trackBusinessView($business);

        $this->assertNotNull($event);
        $this->assertInstanceOf(BusinessAnalyticsEvent::class, $event);
        $this->assertEquals($business->id, $event->business_id);
        $this->assertEquals(BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS, $event->page_type);
        $this->assertEquals($business->id, $event->page_id);
        $this->assertEquals(BusinessAnalyticsEvent::EVENT_VIEW, $event->event);
        $this->assertNull($event->user_id);
        $this->assertNotEmpty($event->visitor_hash);
    }

    /**
     * 2 & 5 & 6 & 7 & 8: Product view creates analytics event with product's business_id.
     */
    public function test_product_view_creates_analytics_event()
    {
        $product = Product::first() ?? Product::factory()->create();

        $event = $this->service->trackProductView($product);

        $this->assertNotNull($event);
        $this->assertInstanceOf(BusinessAnalyticsEvent::class, $event);
        $this->assertEquals($product->business_id, $event->business_id);
        $this->assertEquals(BusinessAnalyticsEvent::PAGE_TYPE_PRODUCT, $event->page_type);
        $this->assertEquals($product->id, $event->page_id);
        $this->assertEquals(BusinessAnalyticsEvent::EVENT_VIEW, $event->event);
    }

    /**
     * 3 & 5 & 6 & 7 & 8: Service view creates analytics event with service's business_id.
     */
    public function test_service_view_creates_analytics_event()
    {
        $service = Service::first() ?? Service::factory()->create();

        $event = $this->service->trackServiceView($service);

        $this->assertNotNull($event);
        $this->assertInstanceOf(BusinessAnalyticsEvent::class, $event);
        $this->assertEquals($service->business_id, $event->business_id);
        $this->assertEquals(BusinessAnalyticsEvent::PAGE_TYPE_SERVICE, $event->page_type);
        $this->assertEquals($service->id, $event->page_id);
        $this->assertEquals(BusinessAnalyticsEvent::EVENT_VIEW, $event->event);
    }

    /**
     * 4 & 5 & 6 & 7 & 8: Expert view creates analytics event with expert's business_id.
     */
    public function test_expert_view_creates_analytics_event()
    {
        $expert = Expert::first() ?? Expert::factory()->create();

        $event = $this->service->trackExpertView($expert);

        $this->assertNotNull($event);
        $this->assertInstanceOf(BusinessAnalyticsEvent::class, $event);
        $this->assertEquals($expert->business_id, $event->business_id);
        $this->assertEquals(BusinessAnalyticsEvent::PAGE_TYPE_EXPERT, $event->page_type);
        $this->assertEquals($expert->id, $event->page_id);
        $this->assertEquals(BusinessAnalyticsEvent::EVENT_VIEW, $event->event);
    }

    /**
     * 9: Guest user stores user_id = NULL.
     */
    public function test_guest_user_stores_user_id_as_null()
    {
        Auth::logout();
        $business = Business::first() ?? Business::factory()->create();

        $event = $this->service->trackBusinessView($business);

        $this->assertNotNull($event);
        $this->assertNull($event->user_id);
    }

    /**
     * 10: Authenticated user stores correct user_id.
     */
    public function test_authenticated_user_stores_correct_user_id()
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user, 'web');

        $business = Business::first() ?? Business::factory()->create();
        $event = $this->service->trackBusinessView($business);

        $this->assertNotNull($event);
        $this->assertEquals($user->id, $event->user_id);
    }

    /**
     * 11: Duplicate immediate view is prevented.
     */
    public function test_duplicate_immediate_view_is_prevented()
    {
        $business = Business::first() ?? Business::factory()->create();

        $firstEvent = $this->service->trackBusinessView($business);
        $this->assertNotNull($firstEvent);

        // Immediate second view on same page by same visitor
        $secondEvent = $this->service->trackBusinessView($business);
        $this->assertNull($secondEvent);
    }

    /**
     * 12: A different page creates a new event.
     */
    public function test_different_page_creates_a_new_event()
    {
        $business = Business::first() ?? Business::factory()->create();
        $product = Product::first() ?? Product::factory()->create();

        $event1 = $this->service->trackBusinessView($business);
        $this->assertNotNull($event1);

        $event2 = $this->service->trackProductView($product);
        $this->assertNotNull($event2);

        $this->assertNotEquals($event1->id, $event2->id);
    }

    /**
     * 13: UTM parameters are captured.
     */
    public function test_utm_parameters_are_captured()
    {
        $business = Business::first() ?? Business::factory()->create();

        $request = Request::create('/test-url?utm_source=google&utm_medium=cpc&utm_campaign=summer_sale', 'GET');

        $event = $this->service->trackView($business, BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS, $business->id, $request);

        $this->assertNotNull($event);
        $this->assertEquals('google', $event->utm_source);
        $this->assertEquals('cpc', $event->utm_medium);
        $this->assertEquals('summer_sale', $event->utm_campaign);
    }

    /**
     * 14: Referer, IP, and Device/Browser/OS information are captured.
     */
    public function test_referer_ip_device_browser_os_captured()
    {
        $business = Business::first() ?? Business::factory()->create();

        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_REFERER'    => 'https://google.com/search',
            'REMOTE_ADDR'     => '192.168.1.100',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        ]);

        $event = $this->service->trackView($business, BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS, $business->id, $request);

        $this->assertNotNull($event);
        $this->assertEquals('https://google.com/search', $event->referer);
        $this->assertEquals('192.168.1.100', $event->ip_address);
        $this->assertEquals(BusinessAnalyticsEvent::DEVICE_MOBILE, $event->device);
        $this->assertEquals('Safari', $event->browser);
        $this->assertEquals('iOS', $event->os);
    }
}
