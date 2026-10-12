<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Peran = template hak akses menu (bukan kolom users.role).
 * users.role tetap menentukan tipe akun (superadmin / admin),
 * sedangkan Peran menentukan menu apa saja yang boleh dibuka oleh admin.
 */
class Peran extends Model
{
    use HasFactory;

    protected $table = 'perans';

    protected $fillable = [
        'nama',
        'deskripsi',
        'menus',
    ];

    protected function casts(): array
    {
        return [
            'menus' => 'array',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Daftar menu yang valid saja (key yang sudah dihapus dari config/menus.php diabaikan).
     */
    public function menuValid(): array
    {
        return array_values(array_intersect($this->menus ?? [], array_keys(config('menus'))));
    }
}