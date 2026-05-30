<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Hold;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HoldCreated
{
    use Dispatchable, SerializesModels;

    public const NAME = 'hold.created';

    public function __construct(public readonly Hold $hold) {}
}
