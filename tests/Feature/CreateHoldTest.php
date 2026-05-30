<?php

declare(strict_types=1);

use App\Models\Hold;
use App\Models\Vehicle;
use App\Enums\HoldStatus;
use App\Events\HoldCreated;

use function Pest\Laravel\postJson;

beforeEach(function () {
    config(['hold.api_key' => 'test-key', 'hold.ttl_minutes' => 15]);
});

test('creates an active hold and returns 201 with the assignment payload shape', function () {
    Event::fake();
    $vehicle = Vehicle::factory()->create();

    $response = postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-987',
    ], ['X-Api-Key' => 'test-key']);

    $response->assertCreated()
        ->assertJsonPath('data.vehicle_id', $vehicle->id)
        ->assertJsonPath('data.buyer_ref', 'lead-987')
        ->assertJsonPath('data.status', HoldStatus::Active->value)
        ->assertJsonStructure(['data' => ['id', 'vehicle_id', 'buyer_ref', 'status', 'expires_at', 'seconds_until_expiry']]);

    expect(Hold::query()->active()->count())->toBe(1);

    // The plaintext token is returned, but only the hash is stored.
    $plain = $response->json('data.release_token');
    expect($plain)->toBeString()->not->toBeEmpty();
    expect(Hold::query()->first()->release_token)->toBe(hash('sha256', $plain));

    Event::assertDispatched(HoldCreated::class);
});

test('returns 409 with active hold details when one already exists', function () {
    $vehicle = Vehicle::factory()->create();
    $existing = Hold::factory()->for($vehicle)->create([
        'buyer_ref' => 'lead-first',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-second',
    ], ['X-Api-Key' => 'test-key']);

    $response->assertStatus(409)
        ->assertJsonPath('error.message', 'ACTIVE_HOLD_EXISTS')
        ->assertJsonPath('error.code', 409)
        ->assertJsonPath('error.details.hold_id', $existing->id)
        ->assertJsonStructure(['error' => ['details' => ['hold_id', 'expires_at', 'seconds_until_expiry']]]);
});

test('a fresh hold can be placed after the previous one is released', function () {
    $vehicle = Vehicle::factory()->create();
    Hold::factory()->for($vehicle)->released()->create();

    postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-2',
    ], ['X-Api-Key' => 'test-key'])->assertCreated();
});

test('returns 400 without an api key', function () {
    $vehicle = Vehicle::factory()->create();

    postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-1',
    ])->assertStatus(400)->assertJsonPath('message', 'API_KEY_MISSING');
});

test('returns 401 with an incorrect api key', function () {
    $vehicle = Vehicle::factory()->create();

    postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-1',
    ], ['X-Api-Key' => 'wrong-key'])->assertStatus(401);
});