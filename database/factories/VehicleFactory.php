<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vin' => Str::uuid()->toString(),
            'name' => Arr::random([
                'DAF XF',
                'DAF XG',
                'DAF XG+',
                'DAF CF',
                'DAF LF',
                'DAF 95',
                'DAF 95XF',
                'DAF 105',
                'DAF 106',
                'DAF FA'
            ]),
        ];
    }
}
