<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HomeLocationFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected BusinessCategory $categoryNearby;
    protected BusinessCategory $categoryFar;
    protected Business $businessSurat;
    protected Business $businessMumbai;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test categories
        $this->categoryNearby = BusinessCategory::create([
            'name' => 'Surat Local Cat ' . uniqid(),
            'slug' => 'surat-local-cat-' . uniqid(),
            'status' => 'active',
        ]);

        $this->categoryFar = BusinessCategory::create([
            'name' => 'Mumbai Local Cat ' . uniqid(),
            'slug' => 'mumbai-local-cat-' . uniqid(),
            'status' => 'active',
        ]);

        // Unique test location 1: ~27.1234, 78.1234
        $this->businessSurat = Business::create([
            'name' => 'Surat Store ' . uniqid(),
            'slug' => 'surat-store-' . uniqid(),
            'business_type' => 'Product',
            'business_category_id' => $this->categoryNearby->id,
            'city_id' => 1,
            'state_id' => 1,
            'address' => 'Surat, Gujarat',
            'area' => 'Vesu',
            'pincode' => '395007',
            'contact' => '99999' . rand(10000, 99999),
            'latitude' => 27.1234,
            'longitude' => 78.1234,
            'status' => 'active',
            'rating' => 5.0,
        ]);

        BusinessSetting::create([
            'business_id' => $this->businessSurat->id,
            'is_private' => false,
        ]);

        // Unique test location 2: ~18.1234, 72.1234 (~1,100 km away)
        $this->businessMumbai = Business::create([
            'name' => 'Mumbai Store ' . uniqid(),
            'slug' => 'mumbai-store-' . uniqid(),
            'business_type' => 'Product',
            'business_category_id' => $this->categoryFar->id,
            'city_id' => 2,
            'state_id' => 2,
            'address' => 'Mumbai, Maharashtra',
            'area' => 'Bandra',
            'pincode' => '400050',
            'contact' => '88888' . rand(10000, 99999),
            'latitude' => 18.1234,
            'longitude' => 72.1234,
            'status' => 'active',
            'rating' => 5.0,
        ]);

        BusinessSetting::create([
            'business_id' => $this->businessMumbai->id,
            'is_private' => false,
        ]);
    }

    /**
     * Test home API with latitude and longitude headers filters nearby businesses.
     */
    public function test_home_api_filters_by_lat_lng_headers(): void
    {
        $response = $this->withHeaders([
            'X-Latitude' => '27.1230',
            'X-Longitude' => '78.1230',
            'X-Radius' => '50',
        ])->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $featuredBusinessIds = collect($data['featured_businesses'])->pluck('id')->toArray();

        $this->assertContains($this->businessSurat->id, $featuredBusinessIds);
        $this->assertNotContains($this->businessMumbai->id, $featuredBusinessIds);
    }

    /**
     * Test home API with area_lat_long boundary header.
     */
    public function test_home_api_filters_by_area_lat_long_header(): void
    {
        // Bounding box around location 1
        $swLat = 27.1000;
        $swLng = 78.1000;
        $neLat = 27.2000;
        $neLng = 78.2000;
        $areaLatLong = "{$swLat},{$swLng},{$neLat},{$neLng}";

        $response = $this->withHeaders([
            'X-Area-Lat-Long' => $areaLatLong,
        ])->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $featuredBusinessIds = collect($data['featured_businesses'])->pluck('id')->toArray();

        $this->assertContains($this->businessSurat->id, $featuredBusinessIds);
        $this->assertNotContains($this->businessMumbai->id, $featuredBusinessIds);
    }

    /**
     * Test home API filters using query parameters as fallback to headers.
     */
    public function test_home_api_filters_by_query_parameters(): void
    {
        $response = $this->getJson('/api/v1/home?latitude=27.1230&longitude=78.1230&radius=50');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $featuredBusinessIds = collect($data['featured_businesses'])->pluck('id')->toArray();

        $this->assertContains($this->businessSurat->id, $featuredBusinessIds);
        $this->assertNotContains($this->businessMumbai->id, $featuredBusinessIds);
    }

    /**
     * Test businesses API with location headers filters by proximity.
     */
    public function test_businesses_api_filters_by_location_headers(): void
    {
        $response = $this->withHeaders([
            'X-Latitude' => '27.1230',
            'X-Longitude' => '78.1230',
            'X-Radius' => '50',
        ])->getJson('/api/v1/businesses');

        $response->assertStatus(200);

        $businesses = $response->json('data.businesses');
        $businessIds = collect($businesses)->pluck('id')->toArray();

        $this->assertContains($this->businessSurat->id, $businessIds);
        $this->assertNotContains($this->businessMumbai->id, $businessIds);
    }

    /**
     * Test businesses API response structure contains both data and businesses keys with pagination.
     */
    public function test_businesses_api_response_structure(): void
    {
        $response = $this->getJson('/api/v1/businesses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'businesses',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);
    }

    /**
     * Test businesses API filters by category and search.
     */
    public function test_businesses_api_filters_by_category_and_search(): void
    {
        $response = $this->getJson('/api/v1/businesses?category_id=' . $this->categoryNearby->id . '&search=' . urlencode($this->businessSurat->name));

        $response->assertStatus(200);
        $businesses = $response->json('data.businesses');
        $businessIds = collect($businesses)->pluck('id')->toArray();

        $this->assertContains($this->businessSurat->id, $businessIds);
        $this->assertNotContains($this->businessMumbai->id, $businessIds);
    }
}
