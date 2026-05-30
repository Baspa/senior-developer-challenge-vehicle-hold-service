<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Hold;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Hold
 */
class HoldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $plainReleaseToken = $this->resource->getAttribute('plain_release_token');

        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'buyer_ref' => $this->buyer_ref,
            'status' => $this->status->value,
            'expires_at' => $this->expires_at->toIso8601String(),
            'seconds_until_expiry' => $this->secondsUntilExpiry(),
            'created_at' => $this->created_at?->toIso8601String(),
            'release_token' => $this->when($plainReleaseToken !== null, $plainReleaseToken),
        ];
    }
}