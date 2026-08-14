<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VisitorAnalyticsPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_business_user_can_view_visitor_analytics_page()
    {
        $business = Business::where('status', 'active')->first();
        if (!$business) {
            $this->markTestSkipped('No active business found.');
        }

        $user = User::where('business_id', $business->id)->first() ?? User::first();
        if (!$user) {
            $this->markTestSkipped('No user found.');
        }
        $user->business_id = $business->id;
        $user->role = 'Business';

        // Seed an event
        BusinessAnalyticsEvent::create([
            'business_id'  => $business->id,
            'event'        => BusinessAnalyticsEvent::EVENT_VIEW,
            'page_type'    => BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS,
            'page_id'      => $business->id,
            'visitor_hash' => 'hash_test_123',
            'device'       => BusinessAnalyticsEvent::DEVICE_MOBILE,
            'browser'      => 'Chrome',
            'os'           => 'Android',
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($user, 'web')
            ->withSession(['business_id' => $business->id, 'currentBusiness' => ['id' => $business->id]])
            ->get(route('business.visitors'));

        $response->assertStatus(200);
        $response->assertSee('Visitor Analytics');
        $response->assertSee('Total Visitors');
        $response->assertSee('Unique Visitors');
        $response->assertSee("Today's Visitors", false);
        $response->assertSee('Last 7 Days');
        $response->assertSee('Last 30 Days');
        $response->assertSee('Returning');
    }

    public function test_visitor_chart_data_api_returns_all_breakdowns()
    {
        $business = Business::where('status', 'active')->first();
        if (!$business) {
            $this->markTestSkipped('No active business found.');
        }

        $user = User::where('business_id', $business->id)->first() ?? User::first();
        if (!$user) {
            $this->markTestSkipped('No user found.');
        }
        $user->business_id = $business->id;
        $user->role = 'Business';

        // Create events for testing charts
        BusinessAnalyticsEvent::create([
            'business_id'  => $business->id,
            'event'        => BusinessAnalyticsEvent::EVENT_VIEW,
            'page_type'    => BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS,
            'page_id'      => $business->id,
            'visitor_hash' => 'hash_test_chart',
            'device'       => BusinessAnalyticsEvent::DEVICE_MOBILE,
            'browser'      => 'Chrome',
            'referer'      => 'https://google.com',
            'utm_source'   => 'google',
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($user, 'web')
            ->withSession(['business_id' => $business->id, 'currentBusiness' => ['id' => $business->id]])
            ->get(route('business.visitors.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'daily' => ['labels', 'total', 'unique'],
                'monthly' => ['labels', 'views'],
                'referrals' => ['labels', 'data'],
                'devices' => ['labels', 'data'],
                'browsers' => ['labels', 'data'],
            ]
        ]);
    }
}
