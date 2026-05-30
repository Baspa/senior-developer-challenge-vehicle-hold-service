<?php

declare(strict_types=1);

use App\Models\Vehicle;

use function Pest\Laravel\postJson;

beforeEach(function () {
    config(['hold.api_key' => 'expected-key']);
});

test('rejects requests when the header is missing', function () {
    postJson('/api/v1/holds', [
        'vehicle_id' => Vehicle::factory()->create()->id,
        'buyer_ref' => 'lead-1',
    ])->assertStatus(400)->assertJsonPath('message', 'API_KEY_MISSING');
});

test('rejects requests when the key is wrong', function () {
    postJson('/api/v1/holds', [
        'vehicle_id' => Vehicle::factory()->create()->id,
        'buyer_ref' => 'lead-1',
    ], ['X-Api-Key' => 'wrong'])->assertStatus(401);
});

test('accepts requests when the key matches', function () {
    postJson('/api/v1/holds', [
        'vehicle_id' => Vehicle::factory()->create()->id,
        'buyer_ref' => 'lead-1',
    ], ['X-Api-Key' => 'expected-key'])->assertCreated();
});
