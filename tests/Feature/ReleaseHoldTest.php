<?php

declare(strict_types=1);

use App\Enums\HoldStatus;
use App\Models\Hold;
use App\Models\Vehicle;
use App\Events\HoldReleased;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    config(['hold.api_key' => 'test-key', 'hold.ttl_minutes' => 15]);
});

test('releases an active hold and returns 200', function () {
    Event::fake();
    $hold = Hold::factory()->create();

    deleteJson("/api/v1/holds/{$hold->id}", [], ['X-Api-Key' => 'test-key'])
        ->assertOk()
        ->assertJsonPath('data.id', $hold->id)
        ->assertJsonPath('data.status', HoldStatus::Released->value)
        ->assertJsonStructure(['data' => ['id', 'vehicle_id', 'buyer_ref', 'status', 'expires_at', 'seconds_until_expiry']]);

    expect($hold->fresh()->status)->toBe(HoldStatus::Released);
    expect(Hold::query()->active()->count())->toBe(0);

    Event::assertDispatched(HoldReleased::class);
});

test('frees the vehicle so a fresh hold can be placed', function () {
    $vehicle = Vehicle::factory()->create();
    $hold = Hold::factory()->for($vehicle)->create();

    deleteJson("/api/v1/holds/{$hold->id}", [], ['X-Api-Key' => 'test-key'])->assertOk();

    postJson('/api/v1/holds', [
        'vehicle_id' => $vehicle->id,
        'buyer_ref' => 'lead-next',
    ], ['X-Api-Key' => 'test-key'])->assertCreated();
});

test('is idempotent when the hold is already released', function () {
    Event::fake();
    $hold = Hold::factory()->released()->create();

    deleteJson("/api/v1/holds/{$hold->id}", [], ['X-Api-Key' => 'test-key'])
        ->assertOk()
        ->assertJsonPath('data.status', HoldStatus::Released->value);

    expect($hold->fresh()->status)->toBe(HoldStatus::Released);
    Event::assertNotDispatched(HoldReleased::class);
});

test('requires an api key', function () {
    $hold = Hold::factory()->create();

    deleteJson("/api/v1/holds/{$hold->id}")
        ->assertStatus(400)
        ->assertJsonPath('message', 'API_KEY_MISSING');

    expect($hold->fresh()->status)->toBe(HoldStatus::Active);
});

test('rejects an incorrect api key', function () {
    $hold = Hold::factory()->create();

    deleteJson("/api/v1/holds/{$hold->id}", [], ['X-Api-Key' => 'wrong-key'])
        ->assertStatus(401);

    expect($hold->fresh()->status)->toBe(HoldStatus::Active);
});

test('returns 404 for an unknown hold', function () {
    deleteJson('/api/v1/holds/019e7261-ee84-7358-be1a-000000000000', [], ['X-Api-Key' => 'test-key'])
        ->assertNotFound();
});
