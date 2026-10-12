@extends('layouts.admin')

@section('title', 'Tambah Data Perjalanan Truk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.waste-trips.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Data Perjalanan Truk</h1>
            <p class="text-slate-500 text-sm">Isi data pengangkutan sampah</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="font-semibold text-slate-800">Form Data Perjalanan</h3>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.waste-trips.store') }}" method="POST" class="space-y-6" id="tripForm">
                @csrf

                {{-- Tanggal & Nomor Polisi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-slate-700 mb-2">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                        @error('tanggal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
<div>
    <label for="nomor_polisi" class="block text-sm font-medium text-slate-700 mb-2">
        Nomor Polisi <span class="text-red-500">*</span>
    </label>
    <select name="nomor_polisi" id="nomor_polisi" required
        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white"
        onchange="fillJenisKendaraan()">
        <option value="">Pilih Armada</option>
        @foreach($armadas as $armada)
            <option value="{{ $armada->nomor_polisi }}"
                    data-jenis="{{ $armada->jenis_kendaraan }}"
                    {{ old('nomor_polisi') == $armada->nomor_polisi ? 'selected' : '' }}>
                {{ $armada->nomor_polisi }} - {{ $armada->nama }}
            </option>
        @endforeach
    </select>
    @if($armadas->isEmpty())
        <p class="mt-1 text-xs text-amber-600">Belum ada armada aktif dengan nomor polisi. Tambahkan dulu di menu Armada.</p>
    @endif
    @error('nomor_polisi')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
                </div>

                {{-- Jenis Kendaraan --}}
<div>
    <label for="jenis_kendaraan" class="block text-sm font-medium text-slate-700 mb-2">
        Jenis Kendaraan <span class="text-red-500">*</span>
    </label>
    <input type="text" name="jenis_kendaraan" id="jenis_kendaraan" value="{{ old('jenis_kendaraan') }}" required
        placeholder="Terisi otomatis dari data armada"
        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
    @error('jenis_kendaraan')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

                {{-- Jam --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-slate-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai') }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                        @error('jam_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-slate-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai') }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                        @error('jam_selesai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Titik Awal --}}
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">1</span>
                        Titik Awal
                    </h4>

                    {{-- Koordinat --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="start_lat" class="block text-sm font-medium text-slate-700 mb-2">
                                Latitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" name="start_lat" id="start_lat" value="{{ old('start_lat') }}" required placeholder="-6.8892"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('start_lat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="start_lng" class="block text-sm font-medium text-slate-700 mb-2">
                                Longitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" name="start_lng" id="start_lng" value="{{ old('start_lng') }}" required placeholder="108.2341"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('start_lng')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="start_nama" class="block text-sm font-medium text-slate-700 mb-2">
                                Nama Titik Awal
                            </label>
                            <input type="text" name="start_nama" id="start_nama" value="{{ old('start_nama') }}" placeholder="Contoh: TPS Cigasong"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('start_nama')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Wilayah: Desa, Kecamatan, Kabupaten, Provinsi --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                        {{-- Provinsi (Readonly) --}}
                        <div>
                            <label class="block text-xs font-medium text-emerald-800 mb-1">
                                Provinsi
                            </label>
                            <input type="text" value="Jawa Barat" readonly
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-emerald-100 text-emerald-700 text-sm cursor-not-allowed">
                            <input type="hidden" name="start_provinsi" value="Jawa Barat">
                        </div>
                        {{-- Kabupaten (Readonly) --}}
                        <div>
                            <label class="block text-xs font-medium text-emerald-800 mb-1">
                                Kabupaten
                            </label>
                            <input type="text" value="Majalengka" readonly
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-emerald-100 text-emerald-700 text-sm cursor-not-allowed">
                            <input type="hidden" name="start_kabupaten" value="Majalengka">
                        </div>
                        {{-- Kecamatan --}}
                        <div>
                            <label class="block text-xs font-medium text-emerald-800 mb-1">
                                Kecamatan <span class="text-red-500">*</span>
                            </label>
                            <select name="start_kecamatan" id="start_kecamatan" required
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white"
                                onchange="updateDesaOptions('start')">
                                <option value="">Pilih Kecamatan</option>
                                @foreach($kecamatanList as $kec)
                                    <option value="{{ $kec }}" {{ old('start_kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                                @endforeach
                            </select>
                            @error('start_kecamatan')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Desa --}}
                        <div>
                            <label class="block text-xs font-medium text-emerald-800 mb-1">
                                Desa <span class="text-red-500">*</span>
                            </label>
                            <select name="start_desa" id="start_desa" required
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white">
                                <option value="">Pilih Desa</option>
                            </select>
                            @error('start_desa')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Pickup Points dengan Drag & Drop --}}
                <div class="border-t border-slate-200 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                            Titik Pickup
                            <span class="text-xs font-normal text-slate-400 ml-2">(Drag handle untuk mengubah urutan)</span>
                        </h4>
                        <button type="button" onclick="addPickupPoint()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Titik
                        </button>
                    </div>

                    <div id="pickup-points-container" class="space-y-3">
                        {{-- Default 1 titik pickup --}}
                        <div class="pickup-point-item bg-slate-50 rounded-lg p-4 border border-slate-200 cursor-move" draggable="true" data-index="0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                                <span class="point-number text-xs font-semibold text-slate-500 uppercase tracking-wide">Titik Pickup #1</span>
                                <div class="flex-1"></div>
                                <button type="button" onclick="removePickupPoint(this)" class="text-red-400 hover:text-red-600 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            {{-- Koordinat & Nama --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Latitude *</label>
                                    <input type="number" step="any" name="pickup_points[0][lat]" required placeholder="-6.8892"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Longitude *</label>
                                    <input type="number" step="any" name="pickup_points[0][lng]" required placeholder="108.2341"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Titik</label>
                                    <input type="text" name="pickup_points[0][nama]" placeholder="Contoh: Pasar Cigasong"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                                </div>
                            </div>
                            {{-- Wilayah Pickup --}}
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Provinsi</label>
                                    <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                                    <input type="hidden" name="pickup_points[0][provinsi]" value="Jawa Barat">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Kabupaten</label>
                                    <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                                    <input type="hidden" name="pickup_points[0][kabupaten]" value="Majalengka">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Kecamatan *</label>
                                    <select name="pickup_points[0][kecamatan]" required class="pickup-kecamatan w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white"
                                        onchange="updatePickupDesa(this)">
                                        <option value="">Pilih Kecamatan</option>
                                        @foreach($kecamatanList as $kec)
                                            <option value="{{ $kec }}">{{ $kec }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Desa *</label>
                                    <select name="pickup_points[0][desa]" required class="pickup-desa w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white">
                                        <option value="">Pilih Desa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('pickup_points')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Titik Akhir --}}
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">3</span>
                        Titik Akhir
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="end_lat" class="block text-sm font-medium text-slate-700 mb-2">
                                Latitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" name="end_lat" id="end_lat" value="{{ old('end_lat') }}" required placeholder="-6.8400"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('end_lat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_lng" class="block text-sm font-medium text-slate-700 mb-2">
                                Longitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" name="end_lng" id="end_lng" value="{{ old('end_lng') }}" required placeholder="108.2500"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('end_lng')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_nama" class="block text-sm font-medium text-slate-700 mb-2">
                                Nama Titik Akhir
                            </label>
                            <input type="text" name="end_nama" id="end_nama" value="{{ old('end_nama') }}" placeholder="Contoh: TPA Sumuradem"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                            @error('end_nama')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Wilayah End --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Provinsi</label>
                            <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-slate-100 text-slate-600 text-sm cursor-not-allowed">
                            <input type="hidden" name="end_provinsi" value="Jawa Barat">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kabupaten</label>
                            <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-slate-100 text-slate-600 text-sm cursor-not-allowed">
                            <input type="hidden" name="end_kabupaten" value="Majalengka">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                            <select name="end_kecamatan" id="end_kecamatan" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white"
                                onchange="updateDesaOptions('end')">
                                <option value="">Pilih Kecamatan</option>
                                @foreach($kecamatanList as $kec)
                                    <option value="{{ $kec }}" {{ old('end_kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                                @endforeach
                            </select>
                            @error('end_kecamatan')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Desa <span class="text-red-500">*</span></label>
                            <select name="end_desa" id="end_desa" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white">
                                <option value="">Pilih Desa</option>
                            </select>
                            @error('end_desa')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="border-t border-slate-200 pt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.waste-trips.index') }}" 
                        class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors text-sm">
                        Batal
                    </a>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition-colors text-sm inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let pickupCount = 1;

// Data desa per kecamatan
const desaData = {
    'Argapura': [
        'Argalingga', 'Argamukti', 'Cibunut', 'Cikaracak', 'Gunungwangi',
        'Haurseah', 'Heubeulisuk', 'Mekarwangi', 'Sadasari', 'Sagara',
        'Sukadana', 'Sukasari Kaler', 'Sukasari Kidul', 'Tejamulya'
    ],
    'Banjaran': [
        'Banjaran', 'Cimeong', 'Darmalarang', 'Genteng', 'Girimulya',
        'Hegarmanah', 'Kagok', 'Kareo', 'Panyindangan', 'Sangiang',
        'Sindangpala', 'Sunia', 'Suniabaru'
    ],
    'Bantarujeg': [
        'Babakansari', 'Bantarujeg', 'Cikidang', 'Cimangguhilir', 'Cinambo',
        'Cipeundeuy', 'Gununglarang', 'Haurgeulis', 'Salawangi', 'Silihwangi',
        'Sindanghurip', 'Sukamenak', 'Wadowetan'
    ],
    'Cigasong': [
        'Baribis', 'Batujaya', 'Karayunan', 'Kawunghilir', 'Kutamanggu',
        'Tajur', 'Tenjolayar', 'Cicenang', 'Cigasong', 'Simpeureum'
    ],
    'Cikijing': [
        'Bagjasari', 'Banjaransari', 'Cidulang', 'Cikijing', 'Cilancang',
        'Cipulus', 'Cisoka', 'Jagasari', 'Kancana', 'Kasturi',
        'Sindang', 'Sindangpanji', 'Sukamukti', 'Sukasari', 'Sunalari'
    ],
    'Cingambul': [
        'Cidadap', 'Cikondang', 'Cimanggugirang', 'Cingambul', 'Cintaasih',
        'Ciranjeng', 'Kondangmekar', 'Maniis', 'Muktisari', 'Nagarakembang',
        'Rawa', 'Sedaraja', 'Wangkelang'
    ],
    'Dawuan': [
        'Balida', 'Baturuyuk', 'Bojongcideres', 'Dawuan', 'Gandu',
        'Genteng', 'Karanganyar', 'Mandapa', 'Pasirmalati', 'Salawana',
        'Sinarjati'
    ],
    'Jatitujuh': [
        'Babajurang', 'Biyawak', 'Jatiraga', 'Jatitengah', 'Jatitujuh',
        'Pangkalanpari', 'Panongan', 'Panyingkiran', 'Pasindangan', 'Pilangsari',
        'Putridalem', 'Randegan Kulon', 'Randegan Wetan', 'Sumber Kulon',
        'Sumber Wetan'
    ],
    'Jatiwangi': [
        'Andir', 'Burujul Kulon', 'Burujul Wetan', 'Cibentar', 'Ciborelang',
        'Cicadas', 'Jatisura', 'Jatiwangi', 'Leuweunggede', 'Loji',
        'Mekarsari', 'Pinangraja', 'Sukaraja Kulon', 'Sukaraja Wetan',
        'Surawangi', 'Sutawangi'
    ],
    'Kadipaten': [
        'Babakan Anyar', 'Cipaku', 'Heuleut', 'Kadipaten', 'Karangsambung',
        'Liangjulang', 'Pagandon'
    ],
    'Kasokandel': [
        'Gandasari', 'Girimukti', 'Gunungsari', 'Jatimulya', 'Jatisawit',
        'Kasokandel', 'Leuwikidang', 'Ranji Kulon', 'Ranji Wetan', 'Wanajaya'
    ],
    'Kertajati': [
        'Babakan', 'Bantarjati', 'Kertajati', 'Kertasari', 'Kertawinangun',
        'Mekarjaya', 'Mekarmulya', 'Pakubeureum', 'Palasah', 'Pasiripis',
        'Sahbandar', 'Sukakerta', 'Sukamulya', 'Sukawana'
    ],
    'Lemahsugih': [
        'Bangbayang', 'Borogojol', 'Cibulan', 'Cigaleuh', 'Cipasung',
        'Kalapadua', 'Kepuh', 'Lemah Putih', 'Margajaya', 'Mekar Wangi',
        'Mekarmulya', 'Padarek', 'Sadawangi', 'Sinargalih', 'Sukajadi',
        'Sukamaju', 'Dayeuhwangi', 'Lemahsugih', 'Cisalak'
    ],
    'Leuwimunding': [
        'Ciparay', 'Heuleut', 'Karangasem', 'Lame', 'Leuwikujang',
        'Leuwimunding', 'Mindi', 'Mirat', 'Nanggerang', 'Parakan',
        'Parungjaya', 'Patuanan', 'Rajawangi', 'Tanjungsari'
    ],
    'Ligung': [
        'Ampel', 'Bantarwaru', 'Beber', 'Beusi', 'Buntu', 'Cibogor',
        'Gandawesi', 'Kedungkancana', 'Kedungsari', 'Kertasari', 'Kodasari',
        'Leuweunghapit', 'Leuwiliang Baru', 'Ligung', 'Ligung Lor',
        'Majasari', 'Sukawera', 'Tegalaren', 'Wanasalam'
    ],
    'Maja': [
        'Anggrawati', 'Banjaran', 'Cengal', 'Cicalung', 'Cieurih', 'Cihaur',
        'Cipicung', 'Maja Selatan', 'Maja Utara', 'Nunuk Baru',
        'Kartabasuki', 'Malongpong', 'Paniis', 'Pasanggrahan', 'Pageraji',
        'Sindangkerta', 'Tegalsari', 'Wanahayu'
    ],
    'Majalengka': [
        'Cibodas', 'Kawunggirang', 'Kulur', 'Sidamukti',
        'Babakan Jawa', 'Cicurug', 'Cijati', 'Cikasarung',
        'Majalengka Kulon', 'Majalengka Wetan', 'Munjul', 'Sindangkasih',
        'Tarikolot', 'Tonjong'
    ],
    'Malausma': [
        'Banyusari', 'Buninagara', 'Cimuncang', 'Ciranca', 'Girimukti',
        'Jagamulya', 'Kramat Jaya', 'Lebakwangi', 'Malausma', 'Sukadana',
        'Werasari'
    ],
    'Palasah': [
        'Buniwangi', 'Cisambeng', 'Enggalwangi', 'Majasuka', 'Palasah',
        'Pasir', 'Sindanghaji', 'Sindangwasa', 'Tarikolot', 'Trajaya',
        'Karamat', 'Waringin', 'Weragati'
    ],
    'Panyingkiran': [
        'Bantrangsana', 'Bonang', 'Cijurey', 'Jatipamor', 'Jatiserang',
        'Karyamukti', 'Leuwiseeng', 'Pasirmuncang', 'Panyingkiran'
    ],
    'Rajagaluh': [
        'Babakankareo', 'Cipinang', 'Cisetu', 'Kumbung', 'Pajajar',
        'Payung', 'Rajagaluh Kidul', 'Rajagaluh Lor', 'Rajagaluh',
        'Sadomas', 'Sindangpano', 'Singawada', 'Teja'
    ],
    'Sindang': [
        'Bayureja', 'Garawastu', 'Gunungkuning', 'Indrakila', 'Pasirayu',
        'Sangkanhurip', 'Sindang'
    ],
    'Sindangwangi': [
        'Balagedog', 'Bantar Agung', 'Buahkapas', 'Jerukleueut',
        'Lengkongkulon', 'Lengkongwetan', 'Leuwilaja', 'Padaherang',
        'Sindangwangi', 'Ujungberung'
    ],
    'Sukahaji': [
        'Babakan Manjeti', 'Candrajaya', 'Cikalong', 'Cikeusik', 'Cikoneng',
        'Ciomas', 'Jayi', 'Nanggewer', 'Padahanten', 'Palabuan',
        'Salagedang', 'Sukahaji', 'Tanjungsari'
    ],
    'Sumberjaya': [
        'Banjaran', 'Bongas Kulon', 'Bongas Wetan', 'Rancaputat', 'Cidenok',
        'Garawangi', 'Gelok Mulya', 'Lojikobong', 'Pancaksuji',
        'Panjalin Kidul', 'Panjalin Lor', 'Paningkiran', 'Parapatan',
        'Sepat', 'Sumberjaya'
    ],
    'Talaga': [
        'Argasari', 'Campaga', 'Cibeureum', 'Cicanir', 'Cikeusal',
        'Ganeas', 'Gunungmanik', 'Jatipamor', 'Kertahayu', 'Lampuyang',
        'Margamukti', 'Mekarhurip', 'Mekarraharja', 'Salado', 'Sukaperna',
        'Talaga Kulon', 'Talaga Wetan'
    ]
};

// Drag & Drop functionality
let draggedItem = null;

function initDragAndDrop() {
    const container = document.getElementById('pickup-points-container');

    container.addEventListener('dragstart', function(e) {
        draggedItem = e.target.closest('.pickup-point-item');
        if (draggedItem) {
            draggedItem.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        }
    });

    container.addEventListener('dragend', function(e) {
        if (draggedItem) {
            draggedItem.style.opacity = '1';
            draggedItem = null;
            reindexPickupPoints();
        }
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(container, e.clientY);
        if (draggedItem) {
            if (afterElement == null) {
                container.appendChild(draggedItem);
            } else {
                container.insertBefore(draggedItem, afterElement);
            }
        }
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.pickup-point-item:not([style*="opacity: 0.5"])')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function addPickupPoint() {
    const container = document.getElementById('pickup-points-container');
    const index = pickupCount;

    const kecamatanOptions = Object.keys(desaData).map(k => `<option value="${k}">${k}</option>`).join('');

    const html = `
        <div class="pickup-point-item bg-slate-50 rounded-lg p-4 border border-slate-200 cursor-move" draggable="true" data-index="${index}">
            <div class="flex items-center gap-3 mb-3">
                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                </div>
                <span class="point-number text-xs font-semibold text-slate-500 uppercase tracking-wide">Titik Pickup #${index + 1}</span>
                <div class="flex-1"></div>
                <button type="button" onclick="removePickupPoint(this)" class="text-red-400 hover:text-red-600 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Latitude *</label>
                    <input type="number" step="any" name="pickup_points[${index}][lat]" required placeholder="-6.8892"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Longitude *</label>
                    <input type="number" step="any" name="pickup_points[${index}][lng]" required placeholder="108.2341"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Titik</label>
                    <input type="text" name="pickup_points[${index}][nama]" placeholder="Contoh: Pasar Cigasong"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm">
                </div>
            </div>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div>
                    <label class="block text-xs font-medium text-blue-800 mb-1">Provinsi</label>
                    <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                    <input type="hidden" name="pickup_points[${index}][provinsi]" value="Jawa Barat">
                </div>
                <div>
                    <label class="block text-xs font-medium text-blue-800 mb-1">Kabupaten</label>
                    <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                    <input type="hidden" name="pickup_points[${index}][kabupaten]" value="Majalengka">
                </div>
                <div>
                    <label class="block text-xs font-medium text-blue-800 mb-1">Kecamatan *</label>
                    <select name="pickup_points[${index}][kecamatan]" required class="pickup-kecamatan w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white"
                        onchange="updatePickupDesa(this)">
                        <option value="">Pilih Kecamatan</option>
                        ${kecamatanOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-blue-800 mb-1">Desa *</label>
                    <select name="pickup_points[${index}][desa]" required class="pickup-desa w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white">
                        <option value="">Pilih Desa</option>
                    </select>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    pickupCount++;
}

function removePickupPoint(btn) {
    const items = document.querySelectorAll('.pickup-point-item');
    if (items.length <= 1) {
        alert('Minimal harus ada 1 titik pickup!');
        return;
    }
    btn.closest('.pickup-point-item').remove();
    reindexPickupPoints();
}

function reindexPickupPoints() {
    const items = document.querySelectorAll('.pickup-point-item');
    items.forEach((item, idx) => {
        item.dataset.index = idx;
        item.querySelector('.point-number').textContent = `Titik Pickup #${idx + 1}`;
        item.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/pickup_points\[\d+\]/, `pickup_points[${idx}]`);
                input.setAttribute('name', newName);
            }
        });
        item.querySelectorAll('select').forEach(select => {
            const name = select.getAttribute('name');
            if (name) {
                const newName = name.replace(/pickup_points\[\d+\]/, `pickup_points[${idx}]`);
                select.setAttribute('name', newName);
            }
        });
    });
    pickupCount = items.length;
}

// ============================================
// DESA DROPDOWN LOGIC
// ============================================

function updateDesaOptions(type) {
    let kecamatanSelect, desaSelect;

    if (type === 'start') {
        kecamatanSelect = document.getElementById('start_kecamatan');
        desaSelect = document.getElementById('start_desa');
    } else if (type === 'end') {
        kecamatanSelect = document.getElementById('end_kecamatan');
        desaSelect = document.getElementById('end_desa');
    }

    if (!kecamatanSelect || !desaSelect) return;

    const kecamatan = kecamatanSelect.value;
    const desaList = desaData[kecamatan] || [];

    // Clear current options
    desaSelect.innerHTML = '<option value="">Pilih Desa</option>';

    // Add desa options
    desaList.forEach(desa => {
        const option = document.createElement('option');
        option.value = desa;
        option.textContent = desa;
        desaSelect.appendChild(option);
    });
}

function updatePickupDesa(kecamatanSelect) {
    const item = kecamatanSelect.closest('.pickup-point-item');
    const desaSelect = item.querySelector('.pickup-desa');

    if (!desaSelect) return;

    const kecamatan = kecamatanSelect.value;
    const desaList = desaData[kecamatan] || [];

    desaSelect.innerHTML = '<option value="">Pilih Desa</option>';

    desaList.forEach(desa => {
        const option = document.createElement('option');
        option.value = desa;
        option.textContent = desa;
        desaSelect.appendChild(option);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();

    // Isi jenis kendaraan bila nopol sudah terpilih (mis. setelah validasi gagal)
    if (document.getElementById('nomor_polisi').value &&
        !document.getElementById('jenis_kendaraan').value) {
        fillJenisKendaraan();
    }

    // Restore old values if validation failed
    @if(old('start_kecamatan'))
        updateDesaOptions('start');
        document.getElementById('start_desa').value = @json(old('start_desa'));
    @endif

    @if(old('end_kecamatan'))
        updateDesaOptions('end');
        document.getElementById('end_desa').value = @json(old('end_desa'));
    @endif
});
function fillJenisKendaraan() {
    const select = document.getElementById('nomor_polisi');
    const jenis  = document.getElementById('jenis_kendaraan');
    const opt    = select.options[select.selectedIndex];
    jenis.value  = opt && opt.dataset.jenis ? opt.dataset.jenis : '';
}
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}

.pickup-point-item {
    transition: transform 0.2s, box-shadow 0.2s;
}

.pickup-point-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.pickup-point-item.dragging {
    opacity: 0.5;
    transform: scale(1.02);
}

.drag-handle {
    touch-action: none;
}
</style>

@endsection