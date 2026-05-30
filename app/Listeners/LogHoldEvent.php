<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\HoldCreated;
use App\Events\HoldExpired;
use App\Events\HoldReleased;
use Illuminate\Support\Facades\Log;
class LogHoldEvent
{
    /**
     * Handle the event.
     */
    public function handle(HoldCreated|HoldExpired|HoldReleased $event): void
    {
        $hold = $event->hold;
        
         Log::channel('holds')->info($event::NAME, [
            'event' => $event::NAME,
            'hold_id' => $hold->id,
            'vehicle_id' => $hold->vehicle_id,
            'buyer_ref' => $hold->buyer_ref,
            'status' => $hold->status->value,
            'expires_at' => $hold->expires_at->toIso8601String(),
        ]);
    }
}
