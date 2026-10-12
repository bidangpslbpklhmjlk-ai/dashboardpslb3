<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Peran = template kumpulan menu. Hanya superadmin (grup route menu:pengguna).
 */
class PeranController extends Controller
{
    public function index()
    {
        $perans = Peran::withCount('users')->orderBy('nama')->get();

        return view('admin.peran.index', compact('perans'));
    }

    public function create()
    {
        return view('admin.peran.create', $this->formData());
    }

    public function store(Request $request)
    {
        Peran::create($this->validated($request));

        return redirect()->route('admin.peran.index')
            ->with('success', 'Peran berhasil ditambahkan.');
    }

    public function edit(Peran $peran)
    {
        $peran->loadCount('users');

        return view('admin.peran.edit', $this->formData() + ['peran' => $peran]);
    }

    public function update(Request $request, Peran $peran)
    {
        $peran->update($this->validated($request, $peran));

        return redirect()->route('admin.peran.index')
            ->with('success', 'Peran berhasil diperbarui. Perubahan menu langsung berlaku untuk semua pengguna peran ini.');
    }

    public function destroy(Peran $peran)
    {
        $dipakai = $peran->users()->count();

        if ($dipakai > 0) {
            return back()->with('error', "Peran ini masih dipakai {$dipakai} pengguna. Pindahkan mereka ke peran lain dulu.");
        }

        $peran->delete();

        return redirect()->route('admin.peran.index')
            ->with('success', 'Peran berhasil dihapus.');
    }

    private function validated(Request $request, ?Peran $peran = null): array
    {
        $data = $request->validate([
            'nama'      => ['required', 'string', 'max:100', Rule::unique('perans', 'nama')->ignore($peran?->id)],
            'deskripsi' => 'nullable|string|max:255',
            'menus'     => 'required|array|min:1',
            'menus.*'   => [Rule::in(array_keys(config('menus')))],
        ], [
            'menus.required' => 'Pilih minimal satu menu.',
            'menus.min'      => 'Pilih minimal satu menu.',
        ]);

        $data['menus'] = array_values($data['menus']);

        return $data;
    }

    private function formData(): array
    {
        return [
            'menuGroups' => collect(config('menus'))
                ->map(fn ($menu, $key) => $menu + ['key' => $key])
                ->groupBy('group'),
        ];
    }
}