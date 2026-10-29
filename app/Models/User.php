<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'peran_id',
        'is_active',
        'menus',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'menus' => 'array',
            'device_bound_at' => 'datetime',
            'last_login_at'   => 'datetime',
            'last_active_at'  => 'datetime',
        ];
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }

    public function loginLogs()
{
    return $this->hasMany(LoginLog::class);
}

/** Teks untuk web admin, misalnya "5 menit yang lalu" atau "Belum pernah". */
public function terakhirAktifLabel(): string
{
    return $this->last_active_at
        ? $this->last_active_at->locale('id')->diffForHumans()
        : 'Belum pernah';
}

    public function peran()
    {
        return $this->belongsTo(Peran::class);
    }

    public function isSuperadmin(): bool
    {
        return $this->is_active && $this->role === 'superadmin';
    }

    /**
     * Menu efektif pengguna: mengikuti peran kalau ada,
     * kalau tidak memakai daftar menu kustom (users.menus).
     */
    public function menuAktif(): array
    {
        if ($this->peran) {
            return $this->peran->menus ?? [];
        }

        return $this->menus ?? [];
    }

    /**
     * True kalau pengguna aktif dan boleh membuka SALAH SATU dari menu yang diberikan.
     */
    public function canAccessMenu(string ...$keys): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->role === 'superadmin') {
            return true;
        }

        return count(array_intersect($keys, $this->menuAktif())) > 0;
    }

    /** Menu (dari config/menus.php) yang boleh diakses pengguna ini. */
    public function allowedMenus(): array
    {
        return collect(config('menus'))
            ->filter(fn ($menu, $key) => $this->canAccessMenu($key))
            ->all();
    }

    /** Nama route halaman pertama yang boleh dibuka (untuk redirect setelah login). */
public function homeRoute(): ?string
{
    return $this->is_active ? 'admin.dashboard' : null;
}

/** Menu yang punya layar di aplikasi Android. Tambah 'checkin' nanti kalau layarnya sudah ada. */
public const MENU_APP = ['gatepass'];

public function menuApp(): array
{
    return array_values(array_filter(self::MENU_APP, fn ($k) => $this->canAccessMenu($k)));
}
}