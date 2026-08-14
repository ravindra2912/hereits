<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BusinessAnalyticsViewTrackingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_api_business_show_records_view_event()
    {
        $business = Business::where('status', 'active')->first();
        if (!$business) {
            $this->markTestSkipped('No active business found.');
        }

        $response = $this->getJson("/api/v1/business/{$business->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('business_analytics_events', [
            'business_id' => $business->id,
            'page_type'   => BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS,
            'page_id'     => $business->id,
            'event'       => BusinessAnalyticsEvent::EVENT_VIEW,
        ]);
    }

    public function test_api_product_details_records_view_event()
    {
        $product = Product::where('status', 'active')->first();
        if (!$product) {
            $this->markTestSkipped('No active product found.');
        }

        $response = $this->getJson("/api/v1/business/product/{$product->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('business_analytics_events', [
            'business_id' => $product->business_id,
            'page_type'   => BusinessAnalyticsEvent::PAGE_TYPE_PRODUCT,
            'page_id'     => $product->id,
            'event'       => BusinessAnalyticsEvent::EVENT_VIEW,
        ]);
    }

    public function test_api_service_details_records_view_event()
    {
        $service = Service::where('status', 'active')->first();
        if (!$service) {
            $this->markTestSkipped('No active service found.');
        }

        $response = $this->getJson("/api/v1/business/service/{$service->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('business_analytics_events', [
            'business_id' => $service->business_id,
            'page_type'   => BusinessAnalyticsEvent::PAGE_TYPE_SERVICE,
            'page_id'     => $service->id,
            'event'       => BusinessAnalyticsEvent::EVENT_VIEW,
        ]);
    }

    public function test_api_expert_details_records_view_event()
    {
        $expert = Expert::where('status', 'active')->first();
        if (!$expert) {
            $this->markTestSkipped('No active expert found.');
        }

        $response = $this->getJson("/api/v1/expert/{$expert->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('business_analytics_events', [
            'business_id' => $expert->business_id,
            'page_type'   => BusinessAnalyticsEvent::PAGE_TYPE_EXPERT,
            'page_id'     => $expert->id,
            'event'       => BusinessAnalyticsEvent::EVENT_VIEW,
        ]);
    }
}
