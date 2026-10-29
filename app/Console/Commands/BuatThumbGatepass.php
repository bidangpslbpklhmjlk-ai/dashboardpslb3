<?php

namespace App\Console\Commands;

use App\Models\GatepassLog;
use App\Support\Thumbnail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BuatThumbGatepass extends Command
{
    protected $signature = 'gatepass:thumb';
    protected $description = 'Buat thumbnail untuk foto gatepass yang belum punya';

    public function handle(): int
    {
        $ok = 0; $lewat = 0;

        GatepassLog::whereNotNull('photo')->chunkById(200, function ($logs) use (&$ok, &$lewat) {
            foreach ($logs as $l) {
                if (Storage::disk('public')->exists(Thumbnail::path($l->photo))) { $lewat++; continue; }
                Thumbnail::buat($l->photo) ? $ok++ : $lewat++;
            }
        });

        $this->info("Dibuat: {$ok}, dilewati: {$lewat}");

        return self::SUCCESS;
    }
}