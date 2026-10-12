<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('pegawai.jabatan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $totalMenu = count(config('menus'));

        return view('admin.pengguna.index', compact('users', 'totalMenu'));
    }

    public function create()
    {
        return view('admin.pengguna.create', $this->formData());
    }

    public function store(Request $request)
    {
        [$data, $profil, $pegawaiId] = $this->validated($request);

        DB::transaction(function () use ($data, $profil, $pegawaiId) {
            $user = User::create($data);
            $this->sinkronPegawai($user, $profil, $pegawaiId);
        });

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(User $pengguna)
    {
        $pengguna->load('pegawai');

        return view('admin.pengguna.edit', $this->formData() + ['pengguna' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        [$data, $profil, $pegawaiId] = $this->validated($request, $pengguna);
        $turunPeran = !$data['is_active'] || $data['role'] !== 'superadmin';

        if ($pengguna->id === auth()->id() && $turunPeran) {
            return back()->withInput()
                ->with('error', 'Anda tidak bisa menonaktifkan akun sendiri atau menurunkan peran Anda sendiri.');
        }

        if ($turunPeran && $this->terakhirSuperadmin($pengguna)) {
            return back()->withInput()
                ->with('error', 'Ini superadmin aktif terakhir. Jadikan pengguna lain superadmin dulu.');
        }

        DB::transaction(function () use ($pengguna, $data, $profil, $pegawaiId) {
            $pengguna->update($data);
            $pengguna->load('pegawai');
            $this->sinkronPegawai($pengguna, $profil, $pegawaiId);
        });

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui');
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        if ($this->terakhirSuperadmin($pengguna)) {
            return back()->with('error', 'Superadmin aktif terakhir tidak bisa dihapus.');
        }

try {
    DB::transaction(function () use ($pengguna) {
        // Pegawai tidak ikut terhapus (riwayat penugasan aman); tautan dilepas
        // oleh nullOnDelete, dan statusnya dinonaktifkan agar tidak muncul di dropdown.
        $pengguna->pegawai?->update(['status' => 'nonaktif']);
        $pengguna->delete();
    });
} catch (QueryException $e) {
    return back()->with('error', 'Pengguna ini punya riwayat data (misalnya kunjungan) dan tidak bisa dihapus. Nonaktifkan saja.');
}

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus');
    }

    /**
     * @return array{0: array, 1: array, 2: ?int} [data user, data profil pegawai, id pegawai lama yang ditautkan]
     */
    private function validated(Request $request, ?User $pengguna = null): array
    {
        $request->merge(['is_active' => $request->boolean('is_active')]);

        // Pegawai yang sedang/akan ditautkan (dipakai untuk aturan unik NIP)
        $pegawaiTarget = $pengguna?->pegawai?->id
            ?? ($request->filled('pegawai_id') ? (int) $request->input('pegawai_id') : null);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($pengguna?->id)],
            'password' => ($pengguna ? 'nullable' : 'required') . '|string|min:8|confirmed',
            'role' => 'required|in:superadmin,admin',
            'is_active' => 'boolean',
            'menus' => 'nullable|array',
            'menus.*' => [Rule::in(array_keys(config('menus')))],

            // Data pegawai
            'jabatan_id' => 'required_if:role,admin|nullable|exists:jabatans,id',
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('pegawais', 'nip')->ignore($pegawaiTarget)],
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'pegawai_id' => ['nullable', Rule::exists('pegawais', 'id')->whereNull('user_id')],
        ]);

        $profil = [
            'jabatan_id' => $validated['jabatan_id'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ];
        $pegawaiId = isset($validated['pegawai_id']) ? (int) $validated['pegawai_id'] : null;

        $data = collect($validated)->only(['name', 'email', 'password', 'role', 'is_active'])->all();

        if (empty($data['password'])) {
            unset($data['password']);
        }

$peranId = $data['role'] === 'admin' ? ($validated['peran_id'] ?? null) : null;

$data['peran_id'] = $peranId;
$data['menus'] = ($data['role'] === 'superadmin' || $peranId)
    ? null
    : array_values($validated['menus'] ?? []);

        return [$data, $profil, $pegawaiId];
    }

    /**
     * Samakan data pegawai dengan akun:
     * - superadmin bukan pegawai: baris lama (kalau ada) dinonaktifkan, tidak dihapus
     * - admin: baris pegawai dibuat/diperbarui, atau menautkan pegawai lama yang dipilih
     */
    private function sinkronPegawai(User $user, array $profil, ?int $pegawaiId): void
    {
        $pegawai = $user->pegawai;

        if ($user->role === 'superadmin') {
            $pegawai?->update(['status' => 'nonaktif']);
            return;
        }

        $inti = [
            'nama_lengkap' => $user->name,
            'jabatan_id' => $profil['jabatan_id'],
            'status' => $user->is_active ? 'aktif' : 'nonaktif',
        ];

        if ($pegawai) {
            $pegawai->update($inti + [
                'nip' => $profil['nip'],
                'no_telepon' => $profil['no_telepon'],
                'alamat' => $profil['alamat'],
            ]);
            return;
        }

$lama = $pegawaiId
    ? Pegawai::whereNull('user_id')->lockForUpdate()->find($pegawaiId)
    : null;

if ($pegawaiId && !$lama) {
    throw ValidationException::withMessages([
        'pegawai_id' => 'Pegawai ini baru saja ditautkan ke akun lain. Pilih ulang.',
    ]);
}

if ($lama) {
    // Menautkan pegawai lama: kolom yang dikosongkan di form tidak menimpa data lama
    $isian = array_filter(
        ['nip' => $profil['nip'], 'no_telepon' => $profil['no_telepon'], 'alamat' => $profil['alamat']],
        fn ($v) => $v !== null && $v !== ''
    );
    $lama->update($inti + $isian + ['user_id' => $user->id]);
    return;
}

Pegawai::create($inti + [
    'user_id' => $user->id,
    'nip' => $profil['nip'],
    'no_telepon' => $profil['no_telepon'],
    'alamat' => $profil['alamat'],
]);
    }

    private function terakhirSuperadmin(User $user): bool
    {
        return $user->role === 'superadmin'
            && $user->is_active
            && User::where('role', 'superadmin')->where('is_active', true)->count() <= 1;
    }

    private function formData(): array
    {
        return [
            'menuGroups' => collect(config('menus'))
                ->map(fn ($menu, $key) => $menu + ['key' => $key])
                ->groupBy('group'),
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(),
            'pegawaiTersedia' => Pegawai::whereNull('user_id')->orderBy('nama_lengkap')->get(),
        ];
    }
}