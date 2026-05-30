<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:expire-overdue-holds')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();