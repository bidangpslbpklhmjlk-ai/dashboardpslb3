@extends('layouts.admin')

@section('title', 'Daftar Armada')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Armada</h1>
            <p class="text-slate-500 mt-1">Kelola data armada pengangkut</p>
        </div>
        <button onclick="openModal('create')"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Armada
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Armada</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Aktif</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['aktif'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Dinas LH</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['dinas_lh'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Non-Dinas</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['non_dinas'] }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.armada.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Cari</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm"
                        placeholder="Nama, nomor polisi...">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tipe</label>
                <select name="tipe" class="w-48 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Armada::TIPE as $key => $cfg)
                        <option value="{{ $key }}" {{ request('tipe') == $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Pembayaran</label>
                <select name="pembayaran" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="">Semua</option>
                    <option value="gratis" {{ request('pembayaran') == 'gratis' ? 'selected' : '' }}>Gratis</option>
                    <option value="bayar" {{ request('pembayaran') == 'bayar' ? 'selected' : '' }}>Bayar</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Status</label>
                <select name="aktif" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="">Semua</option>
                    <option value="1" {{ request('aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Kondisi</label>
                <select name="kondisi" class="w-44 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="">Semua</option>
                    <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ request('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="tidak_beroperasi" {{ request('kondisi') == 'tidak_beroperasi' ? 'selected' : '' }}>Tidak Beroperasi</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors text-sm">
                    🔍 Filter
                </button>
                <a href="{{ route('admin.armada.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center gap-2" id="alertSuccess">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2" id="alertError">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Data Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <div class="text-sm text-slate-600">
                Menampilkan <span class="font-semibold text-slate-900">{{ $armadas->firstItem() ?? 0 }} - {{ $armadas->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-900">{{ $armadas->total() }}</span> data
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 w-14 text-center">#</th>
                        <th class="px-5 py-3.5 w-16">Foto</th>
                        <th class="px-5 py-3.5">Nama Armada</th>
                        <th class="px-5 py-3.5">Tipe</th>
                        <th class="px-5 py-3.5">Nomor Polisi</th>
                        <th class="px-5 py-3.5">Jenis</th>
                        <th class="px-5 py-3.5">Tahun</th>
                        <th class="px-5 py-3.5">Kondisi</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="armadaTableBody">
                    @forelse($armadas as $armada)
                    <tr class="hover:bg-slate-50 transition-colors" data-id="{{ $armada->id }}">
                        <td class="px-5 py-4 text-center text-slate-400 text-xs">
                            {{ $loop->iteration + ($armadas->currentPage() - 1) * $armadas->perPage() }}
                        </td>

                        {{-- Foto --}}
                        <td class="px-5 py-4">
                            @if($armada->foto)
                                <img src="{{ $armada->foto_url }}" alt="{{ $armada->nama }}"
                                     class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                            @endif
                        </td>

                        {{-- Nama --}}
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $armada->nama }}</div>
                            <div class="text-xs text-slate-500 mt-0.5 truncate max-w-[220px]" title="{{ $armada->alamat ?? '' }}">
                                {{ $armada->alamat ?? 'Alamat tidak tersedia' }}
                            </div>
                        </td>

                        {{-- Tipe --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $armada->isDinasLh() ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $armada->tipe_label }}
                            </span>
                        </td>

                        {{-- Nomor Polisi --}}
                        <td class="px-5 py-4 font-mono font-semibold text-slate-700 uppercase text-xs tracking-wide">
                            {{ $armada->nomor_polisi ?? '-' }}
                        </td>

                        {{-- Jenis --}}
                        <td class="px-5 py-4 text-slate-600">{{ $armada->jenis_kendaraan ?? '-' }}</td>

                        {{-- Tahun --}}
                        <td class="px-5 py-4 text-slate-600">{{ $armada->tahun_pengadaan ?? '-' }}</td>

                        {{-- Kondisi --}}
                        <td class="px-5 py-4">
                            @php
                                $kondisiStyles = [
                                    'baik' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800'],
                                    'rusak_ringan' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                    'rusak_berat' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                    'tidak_beroperasi' => ['bg' => 'bg-slate-200', 'text' => 'text-slate-700'],
                                ];
                                $ks = $kondisiStyles[$armada->kondisi] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $ks['bg'] }} {{ $ks['text'] }}">
                                {{ $armada->kondisi_label }}
                            </span>
                        </td>

                        {{-- Pembayaran --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $armada->isGratis() ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $armada->pembayaran_label }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            <form action="{{ route('admin.armada.update', $armada) }}" method="POST" class="inline toggle-status-form" data-id="{{ $armada->id }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="nama" value="{{ $armada->nama }}">
                                <input type="hidden" name="tipe" value="{{ $armada->tipe }}">
                                <input type="hidden" name="pembayaran" value="{{ $armada->pembayaran }}">
                                <input type="hidden" name="kategori_object_retribusi_id" value="{{ $armada->kategori_object_retribusi_id }}">
                                <input type="hidden" name="aktif" value="{{ $armada->aktif ? '0' : '1' }}">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-all {{ $armada->aktif ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                    {{ $armada->aktif ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="showDetail({{ $armada->id }})"
                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button onclick="openModal('edit', {{ $armada->id }})"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button"
                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus"
                                    onclick="confirmDelete('{{ $armada->nama }}', '{{ route('admin.armada.destroy', $armada) }}')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-5 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-slate-500">Belum ada data armada.</p>
                            <button onclick="openModal('create')" class="text-emerald-600 hover:underline mt-2 inline-block text-sm">Tambah data pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($armadas->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $armadas->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    @include('components.admin-footer')
</div>

{{-- ==================== MODAL FORM (Create/Edit) ==================== --}}
<div id="formModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900" id="modalTitle">Tambah Armada</h3>
                <button onclick="closeModal()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="armadaForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <div id="methodField"></div>

                {{-- Foto Preview --}}
                <div class="flex justify-center">
                    <div class="relative">
                        <div id="fotoPreview" class="w-32 h-32 rounded-2xl bg-slate-100 flex items-center justify-center border-2 border-dashed border-slate-300 overflow-hidden">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <label for="fotoInput" class="absolute -bottom-2 -right-2 w-8 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </label>
                        <input type="file" id="fotoInput" name="foto" class="hidden" accept="image/*" onchange="previewFoto(this)">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Nama Armada <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                        <p class="text-red-500 text-xs mt-1 hidden" id="error-nama"></p>
                    </div>

                    {{-- Nomor Polisi --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Nomor Polisi</label>
                        <input type="text" name="nomor_polisi" id="nomor_polisi"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm uppercase">
                    </div>

                    {{-- Tipe --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tipe <span class="text-red-500">*</span></label>
                        <select name="tipe" id="tipe" required onchange="handleTipeChange()"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                            @foreach(\App\Models\Armada::TIPE as $key => $cfg)
                                <option value="{{ $key }}">{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pembayaran --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Pembayaran <span class="text-red-500">*</span></label>
                        <select id="pembayaran_select" required
                            onchange="document.getElementById('pembayaran').value = this.value; handleTarifVisibility()"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                            <option value="gratis">Gratis</option>
                            <option value="bayar">Bayar</option>
                        </select>
                        <input type="hidden" name="pembayaran" id="pembayaran" value="gratis">
                    </div>

                    {{-- Tarif (hanya jika pembayaran = bayar) --}}
                    <div id="tarif_field_wrapper" class="hidden md:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tarif <span class="text-red-500">*</span></label>
<select name="kategori_object_retribusi_id" id="tarif_id"
        class="...">
    <option value="">Pilih Tarif</option>
    @foreach($tarifList as $kelompok => $items)
        <optgroup label="{{ $kelompok }}">
            @foreach($items as $t)
                <option value="{{ $t->id }}">
                    {{ $t->kode }} - {{ $t->nama }}
                    (Rp {{ number_format($t->tarif, 0, ',', '.') }} {{ $t->satuan_label }})
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
<p class="text-xs text-slate-400 mt-1">Tarif yang berlaku untuk perhitungan invoice armada ini.</p>
<p class="text-red-500 text-xs mt-1 hidden" id="error-kategori_object_retribusi_id"></p>
                    </div>

                    {{-- Jenis Kendaraan: Select + Input Manual --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Jenis Kendaraan</label>

                        <select id="jenis_kendaraan_select" onchange="handleJenisChange()"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                            <option value="">Pilih Jenis Kendaraan</option>
                            <option value="Dump Truck">Dump Truck</option>
                            <option value="Arm Roll">Arm Roll</option>
                            <option value="Compactor">Compactor</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Truk Sampah">Truk Sampah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>

                        <div id="jenis_manual_wrapper" class="mt-2 hidden">
                            <input type="text" id="jenis_kendaraan_manual"
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm"
                                placeholder="Masukkan jenis kendaraan lainnya..."
                                oninput="updateJenisHidden()">
                        </div>

                        <input type="hidden" name="jenis_kendaraan" id="jenis_kendaraan">
                    </div>

                    {{-- Tahun Pengadaan & Kondisi: hanya untuk tipe aset (Dinas LH) --}}
                    <div id="dinas_only_fields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tahun Pengadaan</label>
                            <input type="number" name="tahun_pengadaan" id="tahun_pengadaan" min="1900" max="{{ date('Y') + 1 }}"
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Kondisi</label>
                            <select name="kondisi" id="kondisi"
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                                <option value="tidak_beroperasi">Tidak Beroperasi</option>
                            </select>
                        </div>
                    </div>

                    {{-- Telepon --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Telepon</label>
                        <input type="text" name="telepon" id="telepon"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
                    </div>
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="2"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm resize-none"></textarea>
                </div>

                {{-- Status (hanya edit) --}}
                <div id="statusField" class="hidden">
                    <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200 cursor-pointer">
                        <input type="checkbox" name="aktif" id="aktif" value="1" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                        <span class="text-sm font-medium text-slate-700">Armada Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="submitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL DETAIL ==================== --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetail()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">Detail Armada</h3>
                <button onclick="closeDetail()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="detailContent" class="p-6">
                {{-- Diisi via JS --}}
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL DELETE ==================== --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDelete()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Konfirmasi Hapus
                </h3>
                <button onclick="closeDelete()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-slate-600 mb-4">Apakah Anda yakin ingin menghapus armada <strong id="deleteName" class="text-slate-900"></strong>?</p>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-sm text-amber-800">Data yang sudah memiliki riwayat gatepass atau invoice tidak dapat dihapus.</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <button onclick="closeDelete()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">Batal</button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== DATA STORE ====================
const armadaData = @json($armadas->keyBy('id'));
const TIPE_CONFIG = @json(\App\Models\Armada::TIPE);

// ==================== JENIS KENDARAAN HANDLER ====================
const JENIS_OPTIONS = ['Dump Truck', 'Arm Roll', 'Compactor', 'Pickup', 'Truk Sampah'];

function handleJenisChange() {
    const select = document.getElementById('jenis_kendaraan_select');
    const manualWrapper = document.getElementById('jenis_manual_wrapper');
    const manualInput = document.getElementById('jenis_kendaraan_manual');
    const hidden = document.getElementById('jenis_kendaraan');

    if (select.value === 'Lainnya') {
        manualWrapper.classList.remove('hidden');
        manualInput.focus();
        hidden.value = manualInput.value;
    } else {
        manualWrapper.classList.add('hidden');
        manualInput.value = '';
        hidden.value = select.value;
    }
}

function updateJenisHidden() {
    const manualInput = document.getElementById('jenis_kendaraan_manual');
    const hidden = document.getElementById('jenis_kendaraan');
    hidden.value = manualInput.value;
}

function setJenisKendaraan(value) {
    const select = document.getElementById('jenis_kendaraan_select');
    const manualWrapper = document.getElementById('jenis_manual_wrapper');
    const manualInput = document.getElementById('jenis_kendaraan_manual');
    const hidden = document.getElementById('jenis_kendaraan');

    if (!value) {
        select.value = '';
        manualWrapper.classList.add('hidden');
        manualInput.value = '';
        hidden.value = '';
        return;
    }

    if (JENIS_OPTIONS.includes(value)) {
        select.value = value;
        manualWrapper.classList.add('hidden');
        manualInput.value = '';
        hidden.value = value;
    } else {
        select.value = 'Lainnya';
        manualWrapper.classList.remove('hidden');
        manualInput.value = value;
        hidden.value = value;
    }
}

// ==================== TIPE HANDLER ====================
// Mengatur field tahun/kondisi (tipe aset) dan kunci pembayaran (tipe gratis)
function handleTipeChange() {
    const tipe = document.getElementById('tipe').value;
    const cfg = TIPE_CONFIG[tipe] || {};
    const pembayaranSelect = document.getElementById('pembayaran_select');
    const pembayaranHidden = document.getElementById('pembayaran');
    const asetFields = document.getElementById('dinas_only_fields');
    const tahunPengadaan = document.getElementById('tahun_pengadaan');
    const kondisi = document.getElementById('kondisi');

    // Tahun & kondisi hanya untuk tipe aset
    if (cfg.aset) {
        asetFields.classList.remove('hidden');
        if (!tahunPengadaan.value) tahunPengadaan.value = new Date().getFullYear();
        if (!kondisi.value) kondisi.value = 'baik';
    } else {
        asetFields.classList.add('hidden');
        tahunPengadaan.value = '';
        kondisi.value = '';
    }

    // Pembayaran dikunci "gratis" untuk tipe gratis
    if (cfg.gratis) {
        pembayaranSelect.value = 'gratis';
        pembayaranHidden.value = 'gratis';
        pembayaranSelect.setAttribute('disabled', 'disabled');
        pembayaranSelect.classList.add('bg-slate-100', 'cursor-not-allowed');
    } else {
        pembayaranSelect.removeAttribute('disabled');
        pembayaranSelect.classList.remove('bg-slate-100', 'cursor-not-allowed');
        pembayaranHidden.value = pembayaranSelect.value;
    }

    handleTarifVisibility();
}

// Tampilkan field tarif hanya jika pembayaran = bayar
function handleTarifVisibility() {
    const pembayaranHidden = document.getElementById('pembayaran');
    const tarifWrapper = document.getElementById('tarif_field_wrapper');
    const tarifSelect = document.getElementById('tarif_id');

    if (pembayaranHidden.value === 'bayar') {
        tarifWrapper.classList.remove('hidden');
        tarifSelect.setAttribute('required', 'required');
    } else {
        tarifWrapper.classList.add('hidden');
        tarifSelect.removeAttribute('required');
        tarifSelect.value = '';
    }
}

// ==================== MODAL FORM ====================
function openModal(type, id = null) {
    const modal = document.getElementById('formModal');
    const form = document.getElementById('armadaForm');
    const title = document.getElementById('modalTitle');
    const submitText = document.getElementById('submitText');
    const methodField = document.getElementById('methodField');
    const statusField = document.getElementById('statusField');

    // Reset form
    form.reset();
    document.getElementById('fotoPreview').innerHTML = `
        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    `;
    document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));

    // Reset jenis kendaraan
    setJenisKendaraan('');

    if (type === 'create') {
        title.textContent = 'Tambah Armada';
        submitText.textContent = 'Simpan';
        form.action = '{{ route("admin.armada.store") }}';
        methodField.innerHTML = '';
        statusField.classList.add('hidden');

        // Reset semua field
        document.getElementById('nama').value = '';
        document.getElementById('nomor_polisi').value = '';
        document.getElementById('tipe').value = 'swasta';
        document.getElementById('pembayaran_select').value = 'bayar';
        document.getElementById('pembayaran').value = 'bayar';
        document.getElementById('telepon').value = '';
        document.getElementById('alamat').value = '';

        // Reset field aset
        document.getElementById('tahun_pengadaan').value = '';
        document.getElementById('kondisi').value = 'baik';
        document.getElementById('tarif_id').value = '';

    } else {
        const data = armadaData[id];
        if (!data) return;

        title.textContent = 'Edit Armada';
        submitText.textContent = 'Perbarui';
        form.action = '{{ url("admin/armada") }}/' + id;
        methodField.innerHTML = '@method("PUT")';
        statusField.classList.remove('hidden');

        // Isi data
        document.getElementById('nama').value = data.nama || '';
        document.getElementById('tarif_id').value = data.kategori_object_retribusi_id || '';
        document.getElementById('nomor_polisi').value = data.nomor_polisi || '';
        document.getElementById('tipe').value = data.tipe;
        document.getElementById('pembayaran_select').value = data.pembayaran;
        document.getElementById('pembayaran').value = data.pembayaran;
        setJenisKendaraan(data.jenis_kendaraan || '');
        document.getElementById('tahun_pengadaan').value = data.tahun_pengadaan || '';
        document.getElementById('kondisi').value = data.kondisi || 'baik';
        document.getElementById('telepon').value = data.telepon || '';
        document.getElementById('alamat').value = data.alamat || '';
        document.getElementById('aktif').checked = data.aktif;

        if (data.foto) {
            document.getElementById('fotoPreview').innerHTML = `<img src="${data.foto_url}" class="w-full h-full object-cover">`;
        }
    }

    // Terapkan visibilitas field sesuai tipe
    handleTipeChange();
    handleTarifVisibility();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('fotoPreview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ==================== MODAL DETAIL ====================
function showDetail(id) {
    const data = armadaData[id];
    if (!data) {
        fetch(`{{ url('admin/armada') }}/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderDetail(res.data);
            } else {
                showAlert('error', res.message || 'Gagal memuat detail armada');
            }
        })
        .catch(() => showAlert('error', 'Terjadi kesalahan saat memuat detail'));
        return;
    }
    renderDetail(data);
}

function renderDetail(data) {
    const kondisiColors = {
        'baik': ['emerald', 'Baik'],
        'rusak_ringan': ['amber', 'Rusak Ringan'],
        'rusak_berat': ['red', 'Rusak Berat'],
        'tidak_beroperasi': ['slate', 'Tidak Beroperasi']
    };
    const [kColor, kLabel] = kondisiColors[data.kondisi] || ['slate', '-'];
    const tipeCfg = TIPE_CONFIG[data.tipe] || {};

    // Info tahun & kondisi hanya untuk tipe aset
    let asetInfo = '';
    if (tipeCfg.aset) {
        asetInfo = `
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Tahun Pengadaan</div>
                    <div class="font-semibold text-slate-900">${data.tahun_pengadaan || '-'}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Kondisi</div>
                    <div class="font-semibold text-${kColor}-600">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-${kColor}-500"></span>
                            ${kLabel}
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    document.getElementById('detailContent').innerHTML = `
        <div class="space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200">
                    ${data.foto ? `<img src="${data.foto_url}" class="w-full h-full object-cover">` :
                    `<svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>`}
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900">${data.nama}</h4>
                    <div class="flex gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-${data.aktif ? 'emerald' : 'slate'}-100 text-${data.aktif ? 'emerald' : 'slate'}-800">
                            ${data.aktif ? 'Aktif' : 'Nonaktif'}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${tipeCfg.label || '-'}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Nomor Polisi</div>
                    <div class="font-semibold text-slate-900 font-mono text-lg">${data.nomor_polisi || '-'}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Jenis Kendaraan</div>
                    <div class="font-semibold text-slate-900">${data.jenis_kendaraan || '-'}</div>
                </div>
            </div>

            ${asetInfo}

             <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-2">Pembayaran</div>
                <div class="font-semibold text-slate-900 flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-${data.pembayaran === 'gratis' ? 'emerald' : 'purple'}-100 text-${data.pembayaran === 'gratis' ? 'emerald' : 'purple'}-800">
                        ${data.pembayaran === 'gratis' ? 'Gratis' : 'Bayar'}
                    </span>
                </div>
                ${data.pembayaran === 'bayar' && data.kategori ? `
                    <p class="text-xs text-slate-600 mt-2">
                        <span class="font-medium">${data.kategori.kode} - ${data.kategori.nama}</span> — Rp ${Number(data.kategori.tarif).toLocaleString('id-ID')} ${({angkut: 'per angkut', bulan: 'per bulan', hari: 'per hari', kamar_bulan: 'per kamar / bulan'})[data.kategori.satuan] || data.kategori.satuan}
                    </p>
                ` : ''}
            </div>

            ${data.telepon ? `
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="text-slate-700">${data.telepon}</span>
            </div>` : ''}

            ${data.alamat ? `
            <div class="flex items-start gap-3 text-sm">
                <svg class="w-4 h-4 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-slate-700">${data.alamat}</span>
            </div>` : ''}

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-2">
                <button onclick="closeDetail(); openModal('edit', ${data.id})" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Edit
                </button>
            </div>
        </div>
    `;

    document.getElementById('detailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ==================== MODAL DELETE ====================
function confirmDelete(name, url) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDelete() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ==================== FORM SUBMIT (AJAX) ====================
document.getElementById('armadaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('submitBtn');
    const originalText = document.getElementById('submitText').textContent;

    // Sinkronisasi jenis_kendaraan ke hidden input
    const jenisSelect = document.getElementById('jenis_kendaraan_select');
    const jenisManual = document.getElementById('jenis_kendaraan_manual');
    const jenisHidden = document.getElementById('jenis_kendaraan');

    if (jenisSelect.value === 'Lainnya') {
        jenisHidden.value = jenisManual.value.trim();
    } else {
        jenisHidden.value = jenisSelect.value;
    }

    btn.disabled = true;
    document.getElementById('submitText').textContent = 'Menyimpan...';

    const formData = new FormData(form);

    // Hapus field bantu yang tidak perlu dikirim
    formData.delete('jenis_kendaraan_select');
    formData.delete('jenis_kendaraan_manual');

    // Tipe non-aset: jangan kirim tahun & kondisi
    const tipe = document.getElementById('tipe').value;
    if (!TIPE_CONFIG[tipe]?.aset) {
        formData.delete('tahun_pengadaan');
        formData.delete('kondisi');
    }

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok) {
            throw new Error(JSON.stringify(data));
        }
        return data;
    })
    .then(res => {
        if (res.success) {
            closeModal();
            showAlert('success', res.message);
            setTimeout(() => location.reload(), 800);
        } else {
            showAlert('error', res.message || 'Terjadi kesalahan');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        let msg = 'Terjadi kesalahan. Silakan coba lagi.';
        try {
            const data = JSON.parse(err.message);
            if (data.data) {
                msg = Object.values(data.data).flat().join('\n');
            } else if (data.errors) {
                msg = Object.values(data.errors).flat().join('\n');
            } else if (data.message) {
                msg = data.message;
            }
        } catch(e) {}
        showAlert('error', msg);
    })
    .finally(() => {
        btn.disabled = false;
        document.getElementById('submitText').textContent = originalText;
    });
});

function showAlert(type, message) {
    const div = document.createElement('div');
    div.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium ${type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'}`;
    div.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/></svg>
        ${message}
    `;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}

// ==================== TOGGLE STATUS (AJAX) ====================
document.querySelectorAll('.toggle-status-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = form.querySelector('button');

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const aktif = form.querySelector('input[name="aktif"]').value === '1';
                form.querySelector('input[name="aktif"]').value = aktif ? '0' : '1';
                btn.className = `inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-all ${aktif ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`;
                btn.textContent = aktif ? 'Aktif' : 'Nonaktif';
                showAlert('success', 'Status diperbarui');
            } else {
                showAlert('error', res.message || 'Gagal memperbarui status');
            }
        })
        .catch(() => showAlert('error', 'Gagal memperbarui status'));
    });
});

// ==================== KEYBOARD SHORTCUTS ====================
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeDetail();
        closeDelete();
    }
});
</script>
<style>
#pembayaran_select:disabled {
    background-color: #f1f5f9;
    color: #64748b;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>
@endsection