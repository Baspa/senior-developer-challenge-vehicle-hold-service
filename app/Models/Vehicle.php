<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\HoldStatus;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'vin',
        'name',
    ];

    /** @return HasMany<Hold, $this> */
    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    /** @return HasOne<Hold, $this> */
    public function activeHold(): HasOne
    {
        return $this->hasOne(Hold::class)->where('status', HoldStatus::Active);
    }
}
