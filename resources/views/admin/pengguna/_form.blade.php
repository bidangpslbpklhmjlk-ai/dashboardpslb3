@php
    $editing = !is_null($pengguna ?? null);
    $pegawaiTertaut = $editing ? $pengguna->pegawai : null;
    $role = old('role', $pengguna->role ?? 'admin');
    $aktif = (bool) old('is_active', $pengguna->is_active ?? true);
    $dipilih = old('menus', $pengguna->menus ?? []);
@endphp

<div class="max-w-3xl mx-auto p-6">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.pengguna.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $editing ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>
            <p class="text-slate-500 mt-1">Akun, data pegawai, dan menu yang boleh diakses</p>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <form action="{{ $editing ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $pengguna->name ?? '') }}" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $pengguna->email ?? '') }}" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Password @if(!$editing)<span class="text-red-500">*</span>@endif</label>
                <input type="password" name="password" {{ $editing ? '' : 'required' }} autocomplete="new-password"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @if($editing)<p class="text-xs text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah password.</p>@endif
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ulangi Password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Peran <span class="text-red-500">*</span></label>
                <select name="role" id="roleSelect" onchange="toggleRoleSections()"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin (menu sesuai pilihan)</option>
                    <option value="superadmin" {{ $role === 'superadmin' ? 'selected' : '' }}>Superadmin (semua menu + pengguna)</option>
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Akun</label>
                <label class="flex items-center gap-2 px-3 py-2.5 border border-slate-300 rounded-lg cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $aktif ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-slate-700">Akun aktif (bisa login)</span>
                </label>
            </div>
        </div>

        <div id="superInfo" class="hidden rounded-lg border border-purple-200 bg-purple-50 p-4 text-sm text-purple-800">
            Superadmin otomatis bisa membuka semua menu, termasuk Manajemen Pengguna. Superadmin tidak dicatat sebagai pegawai dan tidak tampil di Data Pegawai.
        </div>

        {{-- Data pegawai (hanya untuk Admin) --}}
        <div id="pegawaiSection" class="border-t border-slate-200 pt-5 space-y-5">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Data Pegawai</h3>
                <p class="text-xs text-slate-400 mt-0.5">Nama dan status mengikuti akun di atas.</p>
            </div>

            @if(!$pegawaiTertaut && $pegawaiTersedia->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tautkan ke data pegawai yang sudah ada (opsional)</label>
                <select name="pegawai_id" onchange="isiDariPegawai(this)"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">-- Buat data pegawai baru --</option>
                    @foreach($pegawaiTersedia as $pg)
                        <option value="{{ $pg->id }}"
                                data-nama="{{ $pg->nama_lengkap }}" data-jabatan="{{ $pg->jabatan_id }}"
                                data-nip="{{ $pg->nip }}" data-telp="{{ $pg->no_telepon }}" data-alamat="{{ $pg->alamat }}"
                                {{ old('pegawai_id') == $pg->id ? 'selected' : '' }}>
                            {{ $pg->nama_lengkap }}{{ $pg->nip ? ' — ' . $pg->nip : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Pilih kalau orang ini sudah ada di Data Pegawai lama, supaya riwayat penugasannya tetap menempel.</p>
                @error('pegawai_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                    <select name="jabatan_id"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}" {{ old('jabatan_id', $pegawaiTertaut->jabatan_id ?? '') == $jabatan->id ? 'selected' : '' }}>{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    @if($jabatans->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">Belum ada jabatan. <a href="{{ route('admin.jabatan.create') }}" class="underline">Tambah jabatan dulu</a>.</p>
                    @endif
                    @error('jabatan_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">NIP (opsional)</label>
                    <input type="text" name="nip" value="{{ old('nip', $pegawaiTertaut->nip ?? '') }}"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">No. Telepon</label>
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $pegawaiTertaut->no_telepon ?? '') }}"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    @error('no_telepon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat (opsional)</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $pegawaiTertaut->alamat ?? '') }}"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    @error('alamat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div id="menuSection" class="border-t border-slate-200 pt-5">
            <label class="block text-sm font-medium text-slate-700 mb-2">Menu yang boleh diakses</label>
            @error('menus.*')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
            <div class="space-y-4">
                @foreach($menuGroups as $grup => $items)
                <div class="border border-slate-200 rounded-lg">
                    <label class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200 rounded-t-lg cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup(this, {{ $loop->index }})" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">{{ $grup }}</span>
                        <span class="text-xs text-slate-400">(pilih semua)</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4">
                        @foreach($items as $item)
                        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                            <input type="checkbox" name="menus[]" value="{{ $item['key'] }}" data-group="{{ $loop->parent->index }}"
                                {{ in_array($item['key'], $dipilih) ? 'checked' : '' }}
                                class="rounded text-emerald-600 focus:ring-emerald-500">
                            {{ $item['label'] }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.pengguna.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">💾 Simpan</button>
        </div>
    </form>
</div>

<script>
function toggleRoleSections() {
    const superadmin = document.getElementById('roleSelect').value === 'superadmin';
    document.getElementById('menuSection').style.display = superadmin ? 'none' : '';
    document.getElementById('pegawaiSection').style.display = superadmin ? 'none' : '';
    document.getElementById('superInfo').classList.toggle('hidden', !superadmin);
}
function toggleGroup(src, idx) {
    document.querySelectorAll('input[data-group="' + idx + '"]').forEach(c => c.checked = src.checked);
}
// Saat memilih pegawai lama, isi kolom yang masih kosong dari datanya
function isiDariPegawai(sel) {
    const o = sel.selectedOptions[0];
    if (!o || !o.value) return;
    const isi = (name, val) => {
        const el = document.querySelector('[name="' + name + '"]');
        if (el && !el.value) el.value = val || '';
    };
    isi('name', o.dataset.nama);
    isi('jabatan_id', o.dataset.jabatan);
    isi('nip', o.dataset.nip);
    isi('no_telepon', o.dataset.telp);
    isi('alamat', o.dataset.alamat);
}
document.addEventListener('DOMContentLoaded', toggleRoleSections);
</script>