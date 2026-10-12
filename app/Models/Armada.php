<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Armada extends Model
{
    use HasFactory;

    protected $table = 'armadas';

    protected $fillable = [
    'nama',
    'tipe',
    'pembayaran',
    'kategori_object_retribusi_id',
    'nomor_polisi',
    'jenis_kendaraan',
    'tahun_pengadaan',
    'kondisi',
    'foto',
    'alamat',
    'telepon',
    'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'tahun_pengadaan' => 'integer',
    ];

    protected $appends = ['foto_url'];

    const TIPE_DINAS_LH = 'dinas_lh';
    const TIPE_INSTANSI_PEMERINTAH = 'instansi_pemerintah';
    const TIPE_DESA = 'desa';
    const TIPE_BUMN_BUMD = 'bumn_bumd';
    const TIPE_SWASTA = 'swasta';
    const TIPE_LAINNYA = 'lainnya';

    /**
     * Konfigurasi per tipe:
     * - label : teks yang ditampilkan
     * - gratis: true = otomatis gratis (tanpa tarif)
     * - aset  : true = tampilkan field tahun pengadaan & kondisi
     */
    const TIPE = [
        self::TIPE_DINAS_LH => ['label' => 'Dinas LH', 'gratis' => true, 'aset' => true],
        self::TIPE_INSTANSI_PEMERINTAH => ['label' => 'Instansi Pemerintah', 'gratis' => true, 'aset' => false],
        self::TIPE_DESA => ['label' => 'Desa', 'gratis' => false, 'aset' => false],
        self::TIPE_BUMN_BUMD => ['label' => 'BUMN/BUMD', 'gratis' => false, 'aset' => false],
        self::TIPE_SWASTA => ['label' => 'Swasta', 'gratis' => false, 'aset' => false],
        self::TIPE_LAINNYA => ['label' => 'Lainnya', 'gratis' => false, 'aset' => false],
    ];

    const PEMBAYARAN_GRATIS = 'gratis';
    const PEMBAYARAN_BAYAR = 'bayar';

    const KONDISI_BAIK = 'baik';
    const KONDISI_RUSAK_RINGAN = 'rusak_ringan';
    const KONDISI_RUSAK_BERAT = 'rusak_berat';
    const KONDISI_TIDAK_BEROPERASI = 'tidak_beroperasi';

    // Relasi
    public function gatepassLogs()
    {
        return $this->hasMany(GatepassLog::class);
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scope
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeDinasLh($query)
    {
        return $query->where('tipe', self::TIPE_DINAS_LH);
    }

    // Accessor
    public function getFotoUrlAttribute()
    {
        return $this->foto ? asset('storage/' . $this->foto) : asset('images/default-vehicle.png');
    }

    // Helper
    public function isDinasLh()
    {
        return $this->tipe === self::TIPE_DINAS_LH;
    }

    public function isGratis()
    {
        return $this->pembayaran === self::PEMBAYARAN_GRATIS
            || (self::TIPE[$this->tipe]['gratis'] ?? false);
    }

    public function isAset()
    {
        return self::TIPE[$this->tipe]['aset'] ?? false;
    }

    public function getTipeLabelAttribute()
    {
        return self::TIPE[$this->tipe]['label'] ?? '-';
    }

    public function getPembayaranLabelAttribute()
    {
        return $this->isGratis() ? 'Gratis' : 'Bayar';
    }

    public function getKondisiLabelAttribute()
    {
        $labels = [
            self::KONDISI_BAIK => 'Baik',
            self::KONDISI_RUSAK_RINGAN => 'Rusak Ringan',
            self::KONDISI_RUSAK_BERAT => 'Rusak Berat',
            self::KONDISI_TIDAK_BEROPERASI => 'Tidak Beroperasi',
        ];
        return $labels[$this->kondisi] ?? '-';
    }
        public function kategori()
        {
            return $this->belongsTo(KategoriObjectRetribusi::class, 'kategori_object_retribusi_id');
        }
}