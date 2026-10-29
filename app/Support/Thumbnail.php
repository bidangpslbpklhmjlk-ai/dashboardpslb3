<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Thumbnail
{
    public const SISI = 320;

    public static function path(string $photo): string
    {
        return 'gatepass/thumb/' . basename($photo);
    }

    /** Buat thumbnail JPEG (sisi terpanjang 320 px). @return path thumbnail, atau null kalau gagal. */
    public static function buat(string $photo): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($photo) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring($disk->get($photo));
        if (! $src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $skala = min(1, self::SISI / max($w, $h));
        $nw = max(1, (int) round($w * $skala));
        $nh = max(1, (int) round($h * $skala));

        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagejpeg($dst, null, 75);
        $bin = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        $disk->put(self::path($photo), $bin);

        return self::path($photo);
    }

    public static function hapus(?string $photo): void
    {
        if ($photo) {
            Storage::disk('public')->delete(self::path($photo));
        }
    }
}