<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HoldController;

Route::prefix('v1')->group(function () {
    Route::get('/holds/{hold}', [HoldController::class, 'show']);

    Route::middleware('api.key')->group(function () {
        Route::post('/holds', [HoldController::class, 'store']);
        Route::delete('/holds/{hold}', [HoldController::class, 'destroy']);
    });
});