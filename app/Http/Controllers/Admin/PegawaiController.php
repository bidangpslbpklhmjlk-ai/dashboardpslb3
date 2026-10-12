<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Peran;

/**
 * Data pegawai kini bersumber dari Manajemen Pengguna (pegawai.user_id).
 * Menambah pegawai baru dilakukan lewat menu Pengguna; di sini hanya
 * melihat, melengkapi profil, dan impor CSV data lama.
 * Pegawai milik akun superadmin tidak ditampilkan.
 */
class PegawaiController extends Controller
{
    public function index(Request $request)
{
    $pegawais = Pegawai::with(['jabatan', 'user.peran'])
        ->tampil()
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        })
        ->when($request->jabatan_id, function ($query, $jabatanId) {
            $query->where('jabatan_id', $jabatanId);
        })
        ->orderBy('nama_lengkap')
        ->paginate(10)
        ->withQueryString();

    $jabatans = Jabatan::orderBy('nama_jabatan')->get();

    return view('admin.pegawai.index', compact('pegawais', 'jabatans'));
}
private function pastikanTampil(Pegawai $pegawai): void
{
    // menutup akses langsung via URL ke pegawai milik superadmin
    abort_unless(Pegawai::tampil()->whereKey($pegawai->id)->exists(), 404);
}

public function edit(Pegawai $pegawai)
{
    $this->pastikanTampil($pegawai);
    $pegawai->load(['user.peran', 'jabatan']);

    $perans = null;
    if ($pegawai->user_id) {
        // Pegawai dengan akun: hanya peran yang bisa diubah, dan hanya oleh superadmin
        abort_unless(auth()->user()->isSuperadmin(), 403);
        $perans = Peran::orderBy('nama')->get();
    }

    $jabatans = Jabatan::orderBy('nama_jabatan')->get();

    return view('admin.pegawai.edit', compact('pegawai', 'jabatans', 'perans'));
}

public function update(Request $request, Pegawai $pegawai)
{
    $this->pastikanTampil($pegawai);

    if ($pegawai->user_id) {
        abort_unless(auth()->user()->isSuperadmin(), 403);

        $data = $request->validate([
            'peran_id' => 'required|exists:perans,id',
        ]);

        // menus kustom dikosongkan, supaya menu pengguna sepenuhnya mengikuti peran
        $pegawai->user->update([
            'peran_id' => $data['peran_id'],
            'menus'    => null,
        ]);

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Peran ' . $pegawai->nama_lengkap . ' berhasil diperbarui.');
    }

    // Data lama tanpa akun: tetap bisa dilengkapi seperti sebelumnya
    $pegawai->update($request->validate([
        'nama_lengkap' => 'required|string|max:150',
        'nip'          => 'nullable|string|max:50|unique:pegawais,nip,' . $pegawai->id,
        'jabatan_id'   => 'required|exists:jabatans,id',
        'no_telepon'   => 'nullable|string|max:20',
        'alamat'       => 'nullable|string',
        'status'       => 'required|in:aktif,nonaktif',
    ]));

    return redirect()->route('admin.pegawai.index')
        ->with('success', 'Data pegawai berhasil diperbarui.');
}

    public function destroy(Pegawai $pegawai)
    {
         $this->pastikanTampil($pegawai);
        if ($pegawai->user_id) {
            return redirect()->route('admin.pegawai.index')
                ->with('error', 'Pegawai ini terhubung dengan akun pengguna. Nonaktifkan atau hapus lewat Manajemen Pengguna.');
        }

        try {
            $pegawai->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.pegawai.index')
                ->with('error', 'Pegawai ini masih dipakai di data lain (penugasan, object retribusi, dll). Ubah statusnya menjadi nonaktif saja.');
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    /**
     * Download template CSV untuk import data pegawai.
     */
    public function template()
    {
        $filename = 'template-data-pegawai.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['nama_lengkap', 'nip', 'nama_jabatan', 'no_telepon', 'alamat', 'status'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $columns);
            fputcsv($file, [
                'Contoh Nama Pegawai',
                '19800101 199001 1 001',
                'Pemungut',
                '081234567890',
                'Jl. Contoh No. 1, Majalengka',
                'aktif',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data pegawai secara massal dari file CSV.
     * Data yang diimpor belum punya akun; tautkan lewat form Manajemen Pengguna.
     * Kolom wajib: nama_lengkap, nama_jabatan
     * Kolom opsional: nip, no_telepon, alamat, status (default: aktif)
     * Jabatan dicocokkan berdasarkan nama (harus sudah ada di menu Jabatan).
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

        // buang BOM jika ada, lalu baca header
        $header = fgetcsv($handle);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $header = array_map(fn ($h) => strtolower(trim($h)), $header ?: []);

        foreach (['nama_lengkap', 'nama_jabatan'] as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return back()->with('error', "Format CSV tidak sesuai: kolom '{$col}' tidak ditemukan. Silakan gunakan template yang disediakan.");
            }
        }

        $jabatanMap = Jabatan::pluck('id', 'nama_jabatan')
            ->mapWithKeys(fn ($id, $nama) => [strtolower(trim($nama)) => $id])
            ->toArray();

        $imported = 0;
        $skipped  = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // baris kosong
            }

            $row  = array_pad($row, count($header), null);
            $data = array_combine($header, $row);

            $namaLengkap = trim($data['nama_lengkap'] ?? '');
            $namaJabatan = trim($data['nama_jabatan'] ?? '');
            $nip         = trim($data['nip'] ?? '') ?: null;
            $noTelepon   = trim($data['no_telepon'] ?? '') ?: null;
            $alamat      = trim($data['alamat'] ?? '') ?: null;
            $status      = strtolower(trim($data['status'] ?? '')) ?: 'aktif';
            $status      = in_array($status, ['aktif', 'nonaktif']) ? $status : 'aktif';

            if ($namaLengkap === '' || $namaJabatan === '') {
                $skipped[] = "Baris {$rowNumber}: nama_lengkap atau nama_jabatan kosong.";
                continue;
            }

            $jabatanId = $jabatanMap[strtolower($namaJabatan)] ?? null;
            if (!$jabatanId) {
                $skipped[] = "Baris {$rowNumber}: jabatan '{$namaJabatan}' tidak ditemukan. Tambahkan dulu di menu Jabatan.";
                continue;
            }

            if ($nip && Pegawai::where('nip', $nip)->exists()) {
                $skipped[] = "Baris {$rowNumber}: NIP '{$nip}' sudah terdaftar, dilewati.";
                continue;
            }

            Pegawai::create([
                'nama_lengkap' => $namaLengkap,
                'nip'          => $nip,
                'jabatan_id'   => $jabatanId,
                'no_telepon'   => $noTelepon,
                'alamat'       => $alamat,
                'status'       => $status,
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "{$imported} data pegawai berhasil diimport.";
        if (!empty($skipped)) {
            $message .= ' ' . count($skipped) . ' baris dilewati (lihat detail di bawah).';
        }

        return redirect()->route('admin.pegawai.index')
            ->with($imported > 0 ? 'success' : 'error', $message)
            ->with('import_errors', $skipped);
    }
    
}