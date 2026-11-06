<?php

use App\Models\LoginLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



// Hapus riwayat login yang lebih tua dari 3 bulan, setiap pukul 02:00 WIB
Schedule::command('model:prune', ['--model' => LoginLog::class])
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();