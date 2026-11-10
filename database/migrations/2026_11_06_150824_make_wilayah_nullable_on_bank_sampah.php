<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Unit tingkat kabupaten (mis. PKK Kabupaten) tidak punya kecamatan dan desa. */
    public function up(): void
    {
        $kolom = collect(Schema::getColumns('bank_sampah'))->keyBy('name');

        foreach (['kode_kecamatan', 'kode_desa'] as $c) {
            if (isset($kolom[$c])) {
                DB::statement("ALTER TABLE `bank_sampah` MODIFY `$c` {$kolom[$c]['type']} NULL");
            }
        }
    }

    public function down(): void
    {
        // Sengaja kosong: mengembalikan NOT NULL gagal bila sudah ada unit tanpa wilayah.
    }
};