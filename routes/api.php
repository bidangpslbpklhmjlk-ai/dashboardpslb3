<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GatepassController;
use App\Http\Middleware\EnsureGatepassAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', EnsureGatepassAccess::class])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('master', [GatepassController::class, 'master']);
        Route::get('gatepass', [GatepassController::class, 'index']);
        Route::post('gatepass', [GatepassController::class, 'store']);
        Route::put('gatepass/{uuid}', [GatepassController::class, 'update']);
        Route::delete('gatepass/{uuid}', [GatepassController::class, 'destroy']);
        Route::get('gatepass/riwayat', [GatepassController::class, 'riwayat']);
    });
});