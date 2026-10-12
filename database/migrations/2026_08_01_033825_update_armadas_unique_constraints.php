<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old unique constraint on `nama`, if it still exists.
        // Multiple vehicles (e.g. dinas fleet) can legitimately
        // share the same nama, so this should not be globally unique.
        if ($this->indexExists('armadas', 'armadas_nama_unique')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->dropUnique('armadas_nama_unique');
            });
        }

        // nomor_polisi is the real per-vehicle unique identifier.
        // It already has a unique index in this database, so we only
        // create it if it's somehow missing (e.g. fresh install).
        if (!$this->indexExists('armadas', 'armadas_nomor_polisi_unique')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->unique('nomor_polisi', 'armadas_nomor_polisi_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('armadas', 'armadas_nomor_polisi_unique')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->dropUnique('armadas_nomor_polisi_unique');
            });
        }

        if (!$this->indexExists('armadas', 'armadas_nama_unique')) {
            Schema::table('armadas', function (Blueprint $table) {
                $table->unique('nama', 'armadas_nama_unique');
            });
        }
    }

    /**
     * Check whether a given index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEXES FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }
};
