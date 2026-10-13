<?php

use Illuminate\Support\Facades\Route;

// ==========================================
// IMPORT CONTROLLERS
// ==========================================
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PagesController as AdminPagesController;
use App\Http\Controllers\Admin\BeritaController as AdminBeritaController;
use App\Http\Controllers\Admin\AkuntabilitasController;
use App\Http\Controllers\Admin\WasteTripController;
use App\Http\Controllers\Admin\RuteAngkutController;
use App\Http\Controllers\Admin\GatepassController;
use App\Http\Controllers\Admin\ArmadaController;
use App\Http\Controllers\InvoiceCheckController;
use App\Http\Controllers\Admin\TarifController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\IndustriController;
use App\Http\Controllers\Admin\KategoriObjectRetribusiController;
use App\Http\Controllers\Admin\ObjectRetribusiController;
use App\Http\Controllers\Admin\KunjunganController;
use App\Http\Controllers\Admin\LokasiAngkutController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\PenugasanHarianController;
use App\Http\Controllers\Admin\KerusakanArmadaController;
use App\Http\Controllers\Admin\CheckinController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PeranController;
use App\Http\Controllers\Admin\NeracaSampahController;
use App\Http\Controllers\Admin\DashboardController;

// ==========================================
// TES
// ==========================================
Route::get('/tes-admin', function () {
    return "Admin bekerja!";
});

// ==========================================
// RUTE PUBLIK
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/page/{category}/{slug}', [AdminPagesController::class, 'show'])->name('pages.show');
Route::get('/berita/{id}', [BeritaController::class, 'show'])->name('berita.show');

// --- INVOICE VERIFIKASI PUBLIK ---
Route::get('/cek-invoice', [InvoiceCheckController::class, 'index'])->name('invoice.check');
Route::post('/cek-invoice', [InvoiceCheckController::class, 'verify'])->name('invoice.verify')->middleware('throttle:20,1');
Route::get('/invoice/{barcode}', [InvoiceCheckController::class, 'show'])->name('invoice.show')->middleware('throttle:30,1');


// ==========================================
// AUTHENTIKASI ADMIN
// ==========================================
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit')->middleware('throttle:5,1');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');


// ==========================================
// DASHBOARD ADMIN (WAJIB LOGIN)
// Setiap grup memakai middleware menu:<key>. Key ada di config/menus.php
// ==========================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // --- BERANDA & DASHBOARD ---
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MANAJEMEN PENGGUNA (hanya superadmin) ---
    Route::middleware('menu:pengguna')->group(function () {
        Route::resource('pengguna', UserController::class)
            ->except(['show'])
            ->parameters(['pengguna' => 'pengguna']);
        Route::resource('peran', PeranController::class)
        ->except(['show'])
        ->parameters(['peran' => 'peran']);
    });

    // --- MANAJEMEN HALAMAN STATIS ---
    Route::middleware('menu:halaman')->group(function () {
        Route::post('/pages/upload-image', [AdminPagesController::class, 'uploadImage'])->name('pages.upload-image');
        Route::resource('pages', AdminPagesController::class);
    });

    // --- MANAJEMEN BERITA ---
    Route::middleware('menu:berita')->group(function () {
        Route::get('/berita', [AdminBeritaController::class, 'index'])->name('berita.index');
        Route::get('/berita/create', [AdminBeritaController::class, 'create'])->name('berita.create');
        Route::post('/berita', [AdminBeritaController::class, 'store'])->name('berita.store');
        Route::get('/berita/{id}/edit', [AdminBeritaController::class, 'edit'])->name('berita.edit');
        Route::put('/berita/{id}', [AdminBeritaController::class, 'update'])->name('berita.update');
        Route::delete('/berita/{id}', [AdminBeritaController::class, 'destroy'])->name('berita.destroy');
    });

    // --- MANAJEMEN DOKUMEN AKUNTABILITAS ---
    Route::middleware('menu:akuntabilitas')->group(function () {
        Route::get('akuntabilitas/download/{id}', [AkuntabilitasController::class, 'download'])->name('akuntabilitas.download');
        Route::resource('akuntabilitas', AkuntabilitasController::class);
    });

    // --- RUTE PENGANGKUTAN ---
    Route::middleware('menu:rute-angkut')->group(function () {
        Route::resource('rute-angkut', RuteAngkutController::class)
            ->parameters(['rute-angkut' => 'ruteAngkut']);
    });

    // --- GATEPASS ---
    Route::middleware('menu:gatepass')->group(function () {
        Route::get('/gatepass', [GatepassController::class, 'index'])->name('gatepass.index');
        Route::get('/gatepass/create', [GatepassController::class, 'create'])->name('gatepass.create');
        Route::post('/gatepass', [GatepassController::class, 'store'])->name('gatepass.store');
        Route::get('/gatepass/{gatepass}/edit', [GatepassController::class, 'edit'])->name('gatepass.edit');
        Route::put('/gatepass/{gatepass}', [GatepassController::class, 'update'])->name('gatepass.update');
        Route::delete('/gatepass/{gatepass}', [GatepassController::class, 'destroy'])->name('gatepass.destroy');
    });

// --- ARMADA ---
Route::middleware('menu:armada')->group(function () {
    Route::resource('armada', ArmadaController::class)
        ->except(['create', 'edit']);
});

    // --- PENUGASAN HARIAN (kalender, override per tanggal) ---
    Route::middleware('menu:penugasan')->group(function () {
        Route::get('penugasan-harian', [PenugasanHarianController::class, 'index'])->name('penugasan-harian.index');
    });

    // --- CHECK-IN LOKASI ---
    Route::middleware('menu:checkin')->group(function () {
        Route::get('checkin', [CheckinController::class, 'index'])->name('checkin.index');
        Route::post('checkin', [CheckinController::class, 'store'])->name('checkin.store');
        Route::put('checkin/{checkin}', [CheckinController::class, 'update'])->name('checkin.update');
        Route::delete('checkin/{checkin}', [CheckinController::class, 'destroy'])->name('checkin.destroy');
        Route::post('checkin/pengecualian', [CheckinController::class, 'pengecualian'])->name('checkin.pengecualian');
        Route::delete('checkin/pengecualian/{pengecualian}', [CheckinController::class, 'hapusPengecualian'])->name('checkin.pengecualian.hapus');
    });

    // --- ARMADA RUSAK & PENGALIHAN ---
    Route::middleware('menu:kerusakan')->group(function () {
        Route::resource('kerusakan', KerusakanArmadaController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['kerusakan' => 'kerusakan']);
        Route::post('kerusakan/{kerusakan}/alihkan', [KerusakanArmadaController::class, 'alihkan'])->name('kerusakan.alihkan');
        Route::patch('kerusakan/{kerusakan}/selesai', [KerusakanArmadaController::class, 'selesai'])->name('kerusakan.selesai');
    });

    // --- TARIF (hanya tampilan, data dikelola di Kategori Object Retribusi) ---
    Route::middleware('menu:tarif')->group(function () {
        Route::get('tarif', [TarifController::class, 'index'])->name('tarif.index');
    });

    // --- INVOICE / RETRIBUSI ---
    Route::middleware('menu:invoice')->group(function () {
        Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('/invoice/{invoice}/print', [InvoiceController::class, 'print'])->name('invoice.print');
        Route::patch('/invoice/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoice.status');
    });

    // Invoice pungutan bisa dibuka dari menu Retribusi maupun Pungutan
    Route::middleware('menu:invoice,pungutan')->group(function () {
        Route::get('/invoice-pungutan/{invoiceObjectRetribusi}/print', [InvoiceController::class, 'printPungutan'])->name('invoice.pungutan.print');
        Route::patch('/invoice-pungutan/{invoiceObjectRetribusi}/status', [InvoiceController::class, 'updateStatusPungutan'])->name('invoice.pungutan.status');
    });

    // --- KATEGORI & OBJECT RETRIBUSI ---
    Route::middleware('menu:kategori-object-retribusi')->group(function () {
        Route::resource('kategori-object-retribusi', KategoriObjectRetribusiController::class);
    });
    Route::middleware('menu:object-retribusi')->group(function () {
        Route::resource('object-retribusi', ObjectRetribusiController::class);
    });

    // --- PUNGUTAN (KUNJUNGAN) ---
    Route::middleware('menu:pungutan')->group(function () {
        Route::get('pungutan', [KunjunganController::class, 'index'])->name('pungutan.index');
        Route::get('pungutan/create', [KunjunganController::class, 'create'])->name('pungutan.create');
        Route::post('pungutan', [KunjunganController::class, 'store'])->name('pungutan.store');
        Route::get('pungutan/{kunjungan}', [KunjunganController::class, 'show'])->name('pungutan.show');
        Route::delete('pungutan/{kunjungan}', [KunjunganController::class, 'destroy'])->name('pungutan.destroy');
    });

    // Laporan pungutan juga dilink dari halaman Retribusi (Invoice)
    Route::middleware('menu:pungutan,invoice')->group(function () {
        Route::get('pungutan-report', [KunjunganController::class, 'report'])->name('pungutan.report');
        Route::get('pungutan-report/export/excel', [KunjunganController::class, 'exportReportExcel'])->name('pungutan.report.export.excel');
        Route::get('pungutan-report/export/pdf', [KunjunganController::class, 'exportReportPdf'])->name('pungutan.report.export.pdf');
    });

    // --- LOKASI ANGKUT ---
    Route::middleware('menu:lokasi')->group(function () {
        Route::get('/lokasi/template', [LokasiAngkutController::class, 'template'])->name('lokasi.template');
        Route::post('/lokasi/import', [LokasiAngkutController::class, 'import'])->name('lokasi.import');
        Route::resource('lokasi', LokasiAngkutController::class);
    });

    // --- KEPEGAWAIAN ---
    Route::middleware('menu:jabatan')->group(function () {
        Route::resource('jabatan', JabatanController::class)->except(['show']);
    });
    Route::middleware('menu:pegawai')->group(function () {
        Route::get('/pegawai/template', [PegawaiController::class, 'template'])->name('pegawai.template');
        Route::post('/pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
        Route::resource('pegawai', PegawaiController::class)->except(['show', 'create', 'store']);
    });


// --- NERACA SAMPAH INDUSTRI ---
Route::middleware('menu:neraca-sampah')->prefix('neraca-sampah')->name('neraca-sampah.')->group(function () {
    Route::get('/', [NeracaSampahController::class, 'index'])->name('index');
    Route::post('/', [NeracaSampahController::class, 'store'])->name('store');
    Route::get('industri/{industri}', [NeracaSampahController::class, 'industri'])->name('industri');
    Route::get('{laporan}', [NeracaSampahController::class, 'show'])->name('show');
    Route::put('{laporan}', [NeracaSampahController::class, 'update'])->name('update');
    Route::delete('{laporan}', [NeracaSampahController::class, 'destroy'])->name('destroy');
    Route::get('{laporan}/template', [NeracaSampahController::class, 'template'])->name('template');

    // Input harian: satu bulan, satu area per layar
    Route::get('{laporan}/harian/{bulan}/{area}', [NeracaSampahController::class, 'harian'])->name('harian');
    Route::put('{laporan}/harian/{bulan}/{area}', [NeracaSampahController::class, 'simpanHarian'])->name('harian.simpan');

    // Cetak: jenis = neraca | bulanan | area
    Route::get('{laporan}/cetak/{jenis}', [NeracaSampahController::class, 'cetak'])->name('cetak');
    Route::post('{laporan}/import', [NeracaSampahController::class, 'import'])->name('import');
});
// --- MASTER INDUSTRI ---
Route::middleware('menu:industri')->group(function () {
    Route::resource('industri', IndustriController::class)
        ->except(['create', 'edit', 'show'])
        ->parameters(['industri' => 'industri']);
});
});