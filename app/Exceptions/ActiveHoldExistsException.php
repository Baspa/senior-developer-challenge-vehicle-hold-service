<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Hold;
use Exception;
use Illuminate\Http\JsonResponse;

class ActiveHoldExistsException extends Exception
{
    public function __construct(
        public readonly Hold $existingHold
    ) {
        parent::__construct('Active hold already exists for this vehicle.');
    }

    public function render(): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'message' => 'ACTIVE_HOLD_EXISTS',
                'code' => 409,
                'details' => [
                    'hold_id' => $this->existingHold->id,
                    'expires_at' => $this->existingHold->expires_at->toIso8601String(),
                    'seconds_until_expiry' => $this->existingHold->secondsUntilExpiry(),
                ],
            ],
        ], 409);
    }
}