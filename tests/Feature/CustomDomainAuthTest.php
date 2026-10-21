<?php

test('login route with tenant slug does not return 404', function () {
    $response = $this->postJson('/qnex/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'invalid-password',
    ]);

    // Should return 200 with invalid credentials message, not 404 route not found
    $response->assertStatus(200);
    $response->assertJson([
        'success' => false,
        'message' => 'Invalid Credentials!',
    ]);
});

test('root login route returns json response', function () {
    $response = $this->postJson('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'invalid-password',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => false,
        'message' => 'Invalid Credentials!',
    ]);
});

test('registration route with tenant slug does not return 404', function () {
    $response = $this->postJson('/qnex/Register', []);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => false,
    ]);
    expect($response->json('message'))->not->toBe('Requested API route or resource not found.');
});

test('lowercase registration route with tenant slug works', function () {
    $response = $this->postJson('/qnex/register', []);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => false,
    ]);
    expect($response->json('message'))->not->toBe('Requested API route or resource not found.');
});

test('forgot password route with tenant slug does not return 404', function () {
    $response = $this->postJson('/qnex/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
});

test('favorite toggle with tenant slug does not return 404', function () {
    $response = $this->postJson('/qnex/favorite/toggle', []);

    $response->assertStatus(401);
    expect($response->json('message'))->not->toBe('Requested API route or resource not found.');
});

test('book appointment works with slug and route generates slug url', function () {
    expect(route('book.appointment', 'qnex'))->toContain('/qnex/book-appointment');

    $responseWithSlug = $this->postJson('/qnex/book-appointment', []);
    expect($responseWithSlug->status())->not->toBe(404);

    $responseWithoutSlug = $this->postJson('/book-appointment', []);
    expect(in_array($responseWithoutSlug->status(), [404, 405]))->toBeTrue();
});

test('get expert timing works with slug and route generates slug url', function () {
    expect(route('get.expert.timing', 'qnex'))->toContain('/qnex/get-expert-timing');

    $responseWithSlug = $this->postJson('/qnex/get-expert-timing', []);
    expect($responseWithSlug->status())->not->toBe(404);

    $responseWithoutSlug = $this->postJson('/get-expert-timing', []);
    expect(in_array($responseWithoutSlug->status(), [404, 405]))->toBeTrue();
});

test('campaign lead submit works with slug and route generates slug url', function () {
    expect(route('business.campaign-lead.submit', 'qnex'))->toContain('/qnex/campaign-lead/submit');

    $responseWithSlug = $this->postJson('/qnex/campaign-lead/submit', []);
    expect($responseWithSlug->status())->not->toBe(404);

    $responseWithoutSlug = $this->postJson('/campaign-lead/submit', []);
    expect($responseWithoutSlug->status())->toBe(404);
});
