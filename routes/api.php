<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GatepassController;
use App\Http\Controllers\Api\VersiAplikasiController;
use App\Http\Middleware\EnsureCheckinAccess;
use App\Http\Middleware\EnsureDeviceBound;
use App\Http\Middleware\EnsureGatepassAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('versi-aplikasi', [VersiAplikasiController::class, 'terbaru']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // Semua akun aplikasi (TPA maupun sopir)
    Route::middleware(['auth:sanctum', EnsureDeviceBound::class])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // TPA (menu gatepass)
    Route::middleware(['auth:sanctum', EnsureDeviceBound::class, EnsureGatepassAccess::class])->group(function () {
        Route::get('master', [GatepassController::class, 'master']);
        Route::get('gatepass', [GatepassController::class, 'index']);
        Route::post('gatepass', [GatepassController::class, 'store']);
        Route::put('gatepass/{uuid}', [GatepassController::class, 'update']);
        Route::delete('gatepass/{uuid}', [GatepassController::class, 'destroy']);
        Route::get('gatepass/riwayat', [GatepassController::class, 'riwayat']);
    });

    // Check-in sopir (menu checkin). Route-nya ditambahkan setelah CheckinController API dibuat.
    Route::middleware(['auth:sanctum', EnsureDeviceBound::class, EnsureCheckinAccess::class])->group(function () {
        // Route::get('checkin/tugas', [CheckinController::class, 'tugas']);
        // Route::post('checkin', [CheckinController::class, 'store']);
    });
});