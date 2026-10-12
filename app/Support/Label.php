<?php

namespace App\Support;

/** Nama tampilan model tanpa harus tahu nama kolomnya. Tambah kolom Anda di KOLOM bila perlu. */
class Label
{
    private const KOLOM = [
        'nomor_polisi', 'no_polisi', 'plat_nomor', 'nopol',
        'nama_rute', 'nama_lokasi', 'nama', 'name', 'title',
    ];

    public static function of($model, string $fallback = '-'): string
    {
        if (! $model) {
            return $fallback;
        }
        foreach (self::KOLOM as $k) {
            $v = $model->getAttribute($k);
            if (filled($v)) {
                return (string) $v;
            }
        }
        return class_basename($model) . ' #' . $model->getKey();
    }
}
