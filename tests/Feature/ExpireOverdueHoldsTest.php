<?php

declare(strict_types=1);

use App\Enums\HoldStatus;
use App\Events\HoldExpired;
use App\Models\Hold;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\artisan;

test('marks overdue active holds as expired and emits one event per hold', function () {
    Event::fake();

    $overdueOne = Hold::factory()->overdue()->create();
    $overdueTwo = Hold::factory()->overdue()->create();
    $stillActive = Hold::factory()->create(['expires_at' => now()->addMinutes(10)]);
    $alreadyReleased = Hold::factory()->released()->create();

    artisan('app:expire-overdue-holds')->assertSuccessful();

    expect($overdueOne->fresh()->status)->toBe(HoldStatus::Expired);
    expect($overdueTwo->fresh()->status)->toBe(HoldStatus::Expired);
    expect($stillActive->fresh()->status)->toBe(HoldStatus::Active);
    expect($alreadyReleased->fresh()->status)->toBe(HoldStatus::Released);

    Event::assertDispatchedTimes(HoldExpired::class, 2);
});

test('command is a no-op when there are no overdue holds', function () {
    Event::fake();
    Hold::factory()->create(['expires_at' => now()->addMinutes(5)]);

    artisan('app:expire-overdue-holds')->assertSuccessful();

    Event::assertNotDispatched(HoldExpired::class);
});
