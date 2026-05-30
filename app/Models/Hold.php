<?php

namespace App\Models;

use App\Enums\HoldStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hold extends Model
{
    /** @use HasFactory<\Database\Factories\HoldFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'vehicle_id',
        'buyer_ref',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => HoldStatus::class,
        'expires_at' => 'immutable_datetime',
    ];

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function secondsUntilExpiry(): int
    {
        return (int) max(0, now()->diffInSeconds($this->expires_at, false));
    }
}
