@extends('layouts.admin')

@section('title', 'Industri')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Industri</h1>
            <p class="text-slate-500 mt-1">Data induk industri untuk Neraca Sampah dan B3</p>
        </div>
        <button type="button" onclick="openForm()"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Industri
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('admin.industri.index') }}" class="flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, alamat, atau kecamatan"
               class="w-full max-w-sm border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <button type="submit" class="px-4 py-2 text-sm font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg">Cari</button>
        @if($q !== '')
            <a href="{{ route('admin.industri.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama</th>
                        <th class="px-5 py-3.5">Alamat</th>
                        <th class="px-5 py-3.5">Kecamatan</th>
                        <th class="px-5 py-3.5">Desa/Kel.</th>
                        <th class="px-5 py-3.5">Armada</th>
                        <th class="px-5 py-3.5">Telepon</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($industris as $i)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $i->nama }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $i->alamat ?: '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $i->kecamatan ?: '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $i->desa ?: '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">
                            @forelse($armadaText[$i->id] ?? [] as $label)
                                <div class="text-xs">{{ $label }}</div>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $i->telepon ?: '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($i->aktif)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick='openForm(@json($i))'
                                        class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Ubah</button>
                                <form action="{{ route('admin.industri.destroy', $i) }}" method="POST"
                                      onsubmit="return confirm('Hapus industri ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            Belum ada industri. Klik "Tambah Industri" untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($industris->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $industris->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
</div>

{{-- Modal: Tambah / Ubah --}}
<div id="modalIndustri" class="hidden fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 id="modalJudul" class="text-lg font-semibold text-slate-900">Tambah Industri</h2>
            <button type="button" onclick="closeForm()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        <form id="formIndustri" action="{{ route('admin.industri.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="f_nama" value="{{ old('nama') }}" required maxlength="200"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Alamat</label>
                <input type="text" name="alamat" id="f_alamat" value="{{ old('alamat') }}" maxlength="255"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Kecamatan</label>
                    <select name="kecamatan" id="f_kecamatan" onchange="isiDesa()"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Desa/Kelurahan</label>
                    <select name="desa" id="f_desa"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Telepon</label>
                <input type="text" name="telepon" id="f_telepon" value="{{ old('telepon') }}" maxlength="20"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Armada pengangkut ke TPA</label>
                <div id="f_armada_list" class="border border-slate-300 rounded-lg p-3 max-h-44 overflow-y-auto space-y-1.5 text-sm"></div>
                <p class="text-xs text-slate-400 mt-1">Armada dinas (Kantor DLH) tidak ditampilkan. Armada yang sudah dipakai industri lain juga tidak muncul.</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="aktif" id="f_aktif" value="1" checked
                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                Aktif (tampil di pilihan industri)
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeForm()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const urlStore  = @json(route('admin.industri.store'));
    const urlUpdate = @json(route('admin.industri.update', ['industri' => '__ID__']));

    const wilayah = @json($wilayah);
    const oldKecamatan = @json(old('kecamatan'));
    const oldDesa = @json(old('desa'));

    function opsi(select, daftar, terpilih, kosong) {
        const list = [...daftar];
        // Nilai lama yang tidak ada di daftar (data lama) tetap ditampilkan
        if (terpilih && !list.includes(terpilih)) list.push(terpilih);
        select.innerHTML = '';
        select.add(new Option(kosong, ''));
        list.forEach(v => select.add(new Option(v, v, false, v === terpilih)));
        select.value = terpilih || '';
    }

    function isiKecamatan(terpilih = '') {
        opsi(document.getElementById('f_kecamatan'), Object.keys(wilayah), terpilih, '- Pilih kecamatan -');
    }

    function isiDesa(terpilih = '') {
        const kec = document.getElementById('f_kecamatan').value;
        opsi(document.getElementById('f_desa'), wilayah[kec] || [], terpilih, kec ? '- Pilih desa/kelurahan -' : '- Pilih kecamatan dulu -');
    }

    const armadaList     = @json($armadaList);
    const armadaMap      = @json($armadaMap);
    const armadaTerpakai = @json($armadaTerpakai);
    const oldArmada      = @json(old('armada_ids', []));

    function isiArmada(terpilih = [], industriId = null) {
        const box = document.getElementById('f_armada_list');
        const pilih = (terpilih || []).map(Number);
        box.innerHTML = '';

        // Tersedia: belum dipakai industri mana pun, atau dipakai industri yang sedang diubah
        const tersedia = armadaList.filter(a =>
            !(a.id in armadaTerpakai) || armadaTerpakai[a.id] === industriId || pilih.includes(a.id));

        if (!tersedia.length) {
            box.textContent = 'Tidak ada armada yang tersedia.';
            return;
        }

        tersedia.forEach(a => {
            const label = document.createElement('label');
            label.className = 'flex items-center gap-2 text-slate-700';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'armada_ids[]';
            cb.value = a.id;
            cb.checked = pilih.includes(a.id);
            cb.className = 'rounded border-slate-300 text-emerald-600 focus:ring-emerald-500';
            label.append(cb, document.createTextNode(a.label));
            box.appendChild(label);
        });
    }

    function openForm(data = null) {
        const form = document.getElementById('formIndustri');
        document.getElementById('modalJudul').textContent = data ? 'Ubah Industri' : 'Tambah Industri';
        form.action = data ? urlUpdate.replace('__ID__', data.id) : urlStore;
        document.getElementById('formMethod').value = data ? 'PUT' : 'POST';

        if (data) {
            document.getElementById('f_nama').value      = data.nama || '';
            document.getElementById('f_alamat').value    = data.alamat || '';
            isiKecamatan(data.kecamatan || '');
            isiDesa(data.desa || '');
            document.getElementById('f_telepon').value   = data.telepon || '';
            document.getElementById('f_aktif').checked   = !!data.aktif;
            isiArmada(armadaMap[data.id] || [], data.id);
        } else if (!{{ $errors->any() ? 'true' : 'false' }}) {
            form.reset();
            isiKecamatan();
            isiDesa();
            isiArmada();
            document.getElementById('f_aktif').checked = true;
        } else {
            isiKecamatan(oldKecamatan || '');
            isiDesa(oldDesa || '');
            isiArmada(oldArmada, null);
        }
        document.getElementById('modalIndustri').classList.remove('hidden');
    }
    function closeForm() { document.getElementById('modalIndustri').classList.add('hidden'); }
    @if($errors->any()) openForm(); @endif
</script>
@endsection