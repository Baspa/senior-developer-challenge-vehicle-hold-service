<?php

namespace App\Models;

use App\Enums\HoldStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $vehicle_id
 * @property string $buyer_ref
 * @property HoldStatus $status
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Vehicle $vehicle
 */
class Hold extends Model
{
    /** @use HasFactory<\Database\Factories\HoldFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'vehicle_id',
        'buyer_ref',
        'status',
        'expires_at',
        'release_token',
    ];

    protected $casts = [
        'status' => HoldStatus::class,
        'expires_at' => 'immutable_datetime',
    ];

    protected $hidden = [
        'release_token',
    ];

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** @param Builder<Hold> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', HoldStatus::Active);
    }

    /** @param Builder<Hold> $query */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', HoldStatus::Active)
            ->where('expires_at', '<=', now());
    }

    public function secondsUntilExpiry(): int
    {
        return (int) max(0, now()->diffInSeconds($this->expires_at, false));
    }
}
