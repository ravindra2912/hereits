<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiVersioningAndAuthTest extends TestCase
{
    /**
     * Test API V2 ping route for versioning routing verification.
     */
    public function test_api_v2_ping_route_is_accessible(): void
    {
        $response = $this->getJson('/api/v2/ping');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
                'data' => [
                    'version' => 'v2',
                    'status' => 'active',
                ],
            ]);
    }

    /**
     * Test protected V1 profile route returns 401 JSON when unauthenticated.
     */
    public function test_api_v1_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status_code' => 401,
                'message' => 'Unauthenticated or invalid API token.',
            ]);
    }

    /**
     * Test V1 login route returns 422 JSON on validation failure.
     */
    public function test_api_v1_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'status_code' => 422,
            ]);
    }

    /**
     * Test non-existent API route returns 404 JSON.
     */
    public function test_non_existent_api_route_returns_json_404(): void
    {
        $response = $this->getJson('/api/v1/unknown-endpoint');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status_code' => 404,
                'message' => 'Requested API route or resource not found.',
            ]);
    }
}
