<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('gatepass_logs')->update(['ritasi' => 1]);
    }

    public function down(): void
    {
        // data historis tidak bisa dikembalikan otomatis, tidak perlu rollback logic
    }
};