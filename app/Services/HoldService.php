<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\HoldStatus;
use App\Exceptions\ActiveHoldExistsException;
use App\Exceptions\InvalidReleaseTokenException;
use App\Models\Hold;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Events\HoldCreated;
use App\Events\HoldReleased;
use Illuminate\Support\Str;

class HoldService
{
    /** @throws ActiveHoldExistsException */
    public function create(Vehicle $vehicle, string $buyerRef): Hold
    {
        $expiresAt = now()->addMinutes((int) config('hold.ttl_minutes', 15));

        $plainToken = Str::random(40);

        try {
            $hold = DB::transaction(function () use ($vehicle, $buyerRef, $expiresAt, $plainToken): Hold {
                $existing = Hold::query()
                    ->active()
                    ->where('vehicle_id', $vehicle->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    throw new ActiveHoldExistsException($existing);
                }

                return Hold::query()->create([
                    'vehicle_id' => $vehicle->id,
                    'buyer_ref' => $buyerRef,
                    'status' => HoldStatus::Active,
                    'expires_at' => $expiresAt,
                    'release_token' => hash('sha256', $plainToken),
                ]);
            });

            // Expose the token only in the event
            $hold->setAttribute('plain_release_token', $plainToken);

            HoldCreated::dispatch($hold);

            return $hold;
        } catch (QueryException $e) {
            if (! $this->isUniqueViolation($e)) {
                throw $e;
            }

            $existing = Hold::query()->active()->where('vehicle_id', $vehicle->id)->firstOrFail();
            throw new ActiveHoldExistsException($existing);
        }
    }

    /** @throws InvalidReleaseTokenException */
    public function release(Hold $hold, ?string $token): Hold
    {
        if (! $this->tokenMatches($hold, $token)) {
            throw new InvalidReleaseTokenException();
        }

        if ($hold->status !== HoldStatus::Active) {
            return $hold;
        }

        $hold->update(['status' => HoldStatus::Released]);
        
        $hold = $hold->fresh();

        HoldReleased::dispatch($hold);

        return $hold;
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // Check for integrity constraint violation error codes across different database types (PostgreSQL, SQLite).
        // 23055 = PostgreSQL unique_violation, 23000 = SQLite constraint violation
        return in_array((string) $e->getCode(), ['23000', '23505'], true);
    }

     private function tokenMatches(Hold $hold, ?string $token): bool
    {
        if ($token === null || $token === '' || $hold->release_token === null) {
            return false;
        }

        return hash_equals($hold->release_token, hash('sha256', $token));
    }
}