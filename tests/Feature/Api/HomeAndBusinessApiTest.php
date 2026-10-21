<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HomeAndBusinessApiTest extends TestCase
{
    /**
     * Test Home endpoint returns standard format.
     */
    public function test_api_v1_home_returns_standard_structure(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'banners',
                    'categories',
                    'featured_businesses',
                ],
            ]);
    }

    /**
     * Test Categories endpoint returns collection.
     */
    public function test_api_v1_categories_returns_standard_structure(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data',
            ]);
    }

    /**
     * Test Businesses listing endpoint returns pagination.
     */
    public function test_api_v1_businesses_returns_paginated_structure(): void
    {
        $response = $this->getJson('/api/v1/businesses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'businesses',
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
     * Test Business detail returns 404 for non-existent record.
     */
    public function test_api_v1_business_detail_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/business/99999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status_code' => 404,
                'message' => 'Business not found.',
            ]);
    }

    /**
     * Test Business detail returns section-based structure for existing business.
     */
    public function test_api_v1_business_detail_returns_sections_and_settings(): void
    {
        $business = \App\Models\Business::first();
        if (!$business) {
            $this->markTestSkipped('No business exists in the database.');
        }

        $response = $this->getJson('/api/v1/business/' . $business->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    'business' => [
                        'id',
                        'name',
                        'slug',
                    ],
                    'setting' => [
                        'is_ecommerce_system',
                        'is_service_system',
                        'is_appointment_system',
                    ],
                    'banners',
                    'sections',
                    'details',
                    'experts',
                    'galleries',
                    'blogs',
                    'reviews',
                    'timings',
                ],
            ]);
    }

    /**
     * Test protected user profile route requires authentication.
     */
    public function test_api_v1_user_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status_code' => 401,
            ]);
    }
}
