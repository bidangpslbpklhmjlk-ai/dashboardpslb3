<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use Prunable;

    // Tabel hanya punya created_at (diisi database lewat useCurrent), tanpa updated_at
    public $timestamps = false;

    public const SUCCESS         = 'success';
    public const WRONG_PASSWORD  = 'wrong_password';
    public const DEVICE_MISMATCH = 'device_mismatch';
    public const INACTIVE        = 'inactive';
    public const NO_ACCESS = 'no_access';

    protected $fillable = [
        'user_id', 'email', 'status',
        'device_id', 'device_name', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Baris yang dihapus otomatis: lebih tua dari 3 bulan. */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(3));
    }
}