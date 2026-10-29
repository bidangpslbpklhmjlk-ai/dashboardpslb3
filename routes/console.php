<?php

use App\Models\LoginLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Isi jadwal harian 14 hari ke depan setiap pukul 01:00 WIB
Schedule::command('penugasan:generate --hari=14')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

// Hapus riwayat login yang lebih tua dari 3 bulan, setiap pukul 02:00 WIB
Schedule::command('model:prune', ['--model' => LoginLog::class])
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();