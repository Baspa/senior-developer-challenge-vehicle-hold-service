<?php

declare(strict_types=1);

use App\Models\Hold;

use function Pest\Laravel\getJson;

test('returns hold details', function () {
    $hold = Hold::factory()->create(['buyer_ref' => 'lead-321']);

    getJson("/api/v1/holds/{$hold->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $hold->id)
        ->assertJsonPath('data.buyer_ref', 'lead-321')
        ->assertJsonStructure(['data' => ['id', 'vehicle_id', 'buyer_ref', 'status', 'expires_at', 'seconds_until_expiry']]);
});

test('returns 404 for an unknown hold', function () {
    getJson('/api/v1/holds/019e7261-ee84-7358-be1a-000000000000')
        ->assertNotFound();
});

test('show endpoint is publicly accessible (no api key required)', function () {
    $hold = Hold::factory()->create();

    // No X-Api-Key header on purpose — show is read-only per the spec.
    getJson("/api/v1/holds/{$hold->id}")->assertSuccessful();
});
