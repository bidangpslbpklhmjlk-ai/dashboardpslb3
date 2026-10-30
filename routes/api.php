<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GatepassController;
use App\Http\Middleware\EnsureDeviceBound;
use App\Http\Middleware\EnsureGatepassAccess;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VersiAplikasiController;


Route::prefix('v1')->group(function () {
    Route::get('versi-aplikasi', [VersiAplikasiController::class, 'terbaru']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', EnsureDeviceBound::class])
        ->get('me', [AuthController::class, 'me']);

    Route::middleware(['auth:sanctum', EnsureDeviceBound::class, EnsureGatepassAccess::class])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('master', [GatepassController::class, 'master']);
        Route::get('gatepass', [GatepassController::class, 'index']);
        Route::post('gatepass', [GatepassController::class, 'store']);
        Route::put('gatepass/{uuid}', [GatepassController::class, 'update']);
        Route::delete('gatepass/{uuid}', [GatepassController::class, 'destroy']);
        Route::get('gatepass/riwayat', [GatepassController::class, 'riwayat']);
    });
});