<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;

Route::get('/', function () {
    $vehicles = Vehicle::query()
        ->with('activeHold')
        ->orderBy('name')
        ->get();

    return view('dashboard', ['vehicles' => $vehicles]);
});
