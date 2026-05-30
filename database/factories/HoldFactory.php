<?php

namespace Database\Factories;

use App\Models\Hold;
use App\Models\Vehicle;
use App\Enums\HoldStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hold>
 */
class HoldFactory extends Factory
{
    /**
     * The plaintext token behind the default `release_token` hash. Tests that
     * need to release a factory-made hold send this value as `X-Release-Token`.
     */
    public const PLAIN_TOKEN = 'factory-token';
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'buyer_ref' => 'lead-' . fake()->numberBetween(1000, 9999),
            'status' => HoldStatus::Active,
            'expires_at' => now()->addMinutes(15),
            'release_token' => hash('sha256', self::PLAIN_TOKEN),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => HoldStatus::Expired,
            'expires_at' => now()->subMinutes(5),
        ]);
    }

    public function released(): static
    {
        return $this->state(fn () => [
            'status' => HoldStatus::Released,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => HoldStatus::Active,
            'expires_at' => now()->subMinutes(5),
        ]);
    }
}
