<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\HoldCreated;
use App\Events\HoldExpired;
use App\Events\HoldReleased;
use App\Listeners\LogHoldEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(HoldCreated::class, LogHoldEvent::class);
        Event::listen(HoldExpired::class, LogHoldEvent::class);
        Event::listen(HoldReleased::class, LogHoldEvent::class);
    }
}
