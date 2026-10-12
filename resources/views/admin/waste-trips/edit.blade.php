@extends('layouts.admin')

@section('title', 'Edit Data Perjalanan Truk')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.waste-trips.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Data Perjalanan Truk</h1>
            <p class="text-slate-500 mt-1">Edit data perjalanan {{ $wasteTrip->nomor_polisi }} tanggal {{ $wasteTrip->tanggal->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.waste-trips.update', $wasteTrip) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">
            <!-- Row 1: Tanggal & Nomor Polisi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $wasteTrip->tanggal->format('Y-m-d')) }}" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm @error('tanggal') border-red-500 @enderror">
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Polisi <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_polisi" value="{{ old('nomor_polisi', $wasteTrip->nomor_polisi) }}" placeholder="D 1234 ABC" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm uppercase @error('nomor_polisi') border-red-500 @enderror">
                    @error('nomor_polisi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Kendaraan -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kendaraan <span class="text-red-500">*</span></label>
                <select name="jenis_kendaraan" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm @error('jenis_kendaraan') border-red-500 @enderror">
                    <option value="">Pilih Jenis Kendaraan</option>
                    <option value="Dump Truck" {{ old('jenis_kendaraan', $wasteTrip->jenis_kendaraan) == 'Dump Truck' ? 'selected' : '' }}>Dump Truck</option>
                    <option value="Arm Roll" {{ old('jenis_kendaraan', $wasteTrip->jenis_kendaraan) == 'Arm Roll' ? 'selected' : '' }}>Arm Roll</option>
                    <option value="Compactor" {{ old('jenis_kendaraan', $wasteTrip->jenis_kendaraan) == 'Compactor' ? 'selected' : '' }}>Compactor</option>
                    <option value="Pickup" {{ old('jenis_kendaraan', $wasteTrip->jenis_kendaraan) == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="Truk Sampah" {{ old('jenis_kendaraan', $wasteTrip->jenis_kendaraan) == 'Truk Sampah' ? 'selected' : '' }}>Truk Sampah</option>
                </select>
                @error('jenis_kendaraan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Row 2: Jam Mulai & Jam Selesai -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $wasteTrip->jam_mulai) }}" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm @error('jam_mulai') border-red-500 @enderror">
                    @error('jam_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $wasteTrip->jam_selesai) }}" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm @error('jam_selesai') border-red-500 @enderror">
                    @error('jam_selesai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Koordinat Awal dengan Wilayah Manual -->
            <div class="border-t border-slate-200 pt-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">1</span>
                    Titik Awal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Latitude <span class="text-red-500">*</span></label>
                        <input type="number" step="any" name="start_lat" id="start_lat" value="{{ old('start_lat', $wasteTrip->start_lat) }}" placeholder="-6.8300" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('start_lat') border-red-500 @enderror">
                        @error('start_lat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Longitude <span class="text-red-500">*</span></label>
                        <input type="number" step="any" name="start_lng" id="start_lng" value="{{ old('start_lng', $wasteTrip->start_lng) }}" placeholder="108.2300" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('start_lng') border-red-500 @enderror">
                        @error('start_lng')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nama Titik Awal</label>
                        <input type="text" name="start_nama" value="{{ old('start_nama', $wasteTrip->start_nama) }}" placeholder="Contoh: TPS Cigasong"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('start_nama') border-red-500 @enderror">
                        @error('start_nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Wilayah Start -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                    <div>
                        <label class="block text-xs font-medium text-emerald-800 mb-1">Provinsi</label>
                        <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-emerald-100 text-emerald-700 text-sm cursor-not-allowed">
                        <input type="hidden" name="start_provinsi" value="Jawa Barat">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-800 mb-1">Kabupaten</label>
                        <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-emerald-100 text-emerald-700 text-sm cursor-not-allowed">
                        <input type="hidden" name="start_kabupaten" value="Majalengka">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-800 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                        <select name="start_kecamatan" id="start_kecamatan" required
                            class="w-full px-3 py-2 rounded-lg border border-emerald-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-sm bg-white"
                            onchange="updateDesaOptions('start')">
                            <option value="">Pilih Kecamatan</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec }}" {{ old('start_kecamatan', $wasteTrip->start_kecamatan) == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                        @error('start_kecamatan')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-800 mb-1">Desa <span class="text-red-500">*</span></label>
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

            <!-- Titik Pengangkutan dengan Drag & Drop + Wilayah -->
            <div class="border-t border-slate-200 pt-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                        Titik Pengangkutan
                        <span class="text-xs font-normal text-slate-400 ml-2">(Drag handle untuk mengubah urutan)</span>
                    </h3>
                    <button type="button" onclick="addPickupPoint()" 
                        class="text-sm text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Titik
                    </button>
                </div>
                <div id="pickupPoints" class="space-y-3">
                    @php $pickupPoints = old('pickup_points', $wasteTrip->pickup_points); @endphp
                    @foreach($pickupPoints as $index => $point)
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 relative pickup-point cursor-move" draggable="true" data-index="{{ $index }}">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                                <span class="point-number text-xs font-semibold text-slate-600 uppercase tracking-wide">Titik Pengangkutan {{ $index + 1 }}</span>
                                <div class="flex-1"></div>
                                @if($index > 0)
                                    <button type="button" onclick="removePickupPoint(this)" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Latitude <span class="text-red-500">*</span></label>
                                    <input type="number" step="any" name="pickup_points[{{ $index }}][lat]" 
                                        value="{{ $point['lat'] ?? '' }}" placeholder="-6.8350" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-lat">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Longitude <span class="text-red-500">*</span></label>
                                    <input type="number" step="any" name="pickup_points[{{ $index }}][lng]" 
                                        value="{{ $point['lng'] ?? '' }}" placeholder="108.2400" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-lng">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Nama Titik</label>
                                    <input type="text" name="pickup_points[{{ $index }}][nama]" 
                                        value="{{ $point['nama'] ?? '' }}" placeholder="Contoh: Pasar Cigasong"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-nama">
                                </div>
                            </div>
                            <!-- Wilayah Pickup -->
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Provinsi</label>
                                    <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                                    <input type="hidden" name="pickup_points[{{ $index }}][provinsi]" value="Jawa Barat">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Kabupaten</label>
                                    <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                                    <input type="hidden" name="pickup_points[{{ $index }}][kabupaten]" value="Majalengka">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Kecamatan *</label>
                                    <select name="pickup_points[{{ $index }}][kecamatan]" required class="pickup-kecamatan w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white"
                                        onchange="updatePickupDesa(this)">
                                        <option value="">Pilih Kecamatan</option>
                                        @foreach($kecamatanList as $kec)
                                            <option value="{{ $kec }}" {{ ($point['kecamatan'] ?? '') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-blue-800 mb-1">Desa *</label>
                                    <select name="pickup_points[{{ $index }}][desa]" required class="pickup-desa w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white">
                                        <option value="">Pilih Desa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('pickup_points')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
                @error('pickup_points.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Titik Akhir dengan Wilayah Manual -->
            <div class="border-t border-slate-200 pt-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">3</span>
                    Titik Akhir
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Latitude <span class="text-red-500">*</span></label>
                        <input type="number" step="any" name="end_lat" id="end_lat" value="{{ old('end_lat', $wasteTrip->end_lat) }}" placeholder="-6.8400" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('end_lat') border-red-500 @enderror">
                        @error('end_lat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Longitude <span class="text-red-500">*</span></label>
                        <input type="number" step="any" name="end_lng" id="end_lng" value="{{ old('end_lng', $wasteTrip->end_lng) }}" placeholder="108.2500" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('end_lng') border-red-500 @enderror">
                        @error('end_lng')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nama Titik Akhir</label>
                        <input type="text" name="end_nama" value="{{ old('end_nama', $wasteTrip->end_nama) }}" placeholder="Contoh: TPA Sumuradem"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm @error('end_nama') border-red-500 @enderror">
                        @error('end_nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Wilayah End -->
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
                                <option value="{{ $kec }}" {{ old('end_kecamatan', $wasteTrip->end_kecamatan) == $kec ? 'selected' : '' }}>{{ $kec }}</option>
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

            <!-- Total Jarak -->
            <div class="bg-slate-50 rounded-lg p-4 flex justify-between items-center border border-slate-200">
                <span class="text-sm font-medium text-slate-700">Total Jarak (auto-calculated):</span>
                <span class="text-lg font-bold text-emerald-600" id="totalJarakDisplay">{{ number_format($wasteTrip->total_jarak, 2) }} km</span>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.waste-trips.index') }}" 
                class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors">
                Batal
            </a>
            <button type="submit" 
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Data
            </button>
        </div>
    </form>
</div>

<!-- Leaflet Map Preview -->
<div class="max-w-4xl mx-auto mt-6 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 bg-slate-50">
        <h3 class="font-semibold text-slate-800">Preview Peta</h3>
    </div>
    <div id="map" class="h-80 w-full"></div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let pickupPointCount = {{ count($pickupPoints) }};
let map = null;
let markers = [];
let routeLine = null;

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
    const container = document.getElementById('pickupPoints');

    container.addEventListener('dragstart', function(e) {
        draggedItem = e.target.closest('.pickup-point');
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
            calculateDistance();
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
    const draggableElements = [...container.querySelectorAll('.pickup-point:not([style*="opacity: 0.5"])')];

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

// Initialize map
function initMap() {
    const startLat = {{ $wasteTrip->start_lat }};
    const startLng = {{ $wasteTrip->start_lng }};
    map = L.map('map').setView([startLat, startLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    calculateDistance();
}

function addPickupPoint() {
    pickupPointCount++;
    const container = document.getElementById('pickupPoints');
    const kecamatanOptions = Object.keys(desaData).map(k => `<option value="${k}">${k}</option>`).join('');

    const div = document.createElement('div');
    div.className = 'bg-slate-50 rounded-lg p-4 border border-slate-200 relative pickup-point cursor-move';
    div.draggable = true;
    div.dataset.index = pickupPointCount - 1;
    div.innerHTML = `
        <div class="flex items-center gap-3 mb-2">
            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
            </div>
            <span class="point-number text-xs font-semibold text-slate-600 uppercase tracking-wide">Titik Pengangkutan ${pickupPointCount}</span>
            <div class="flex-1"></div>
            <button type="button" onclick="removePickupPoint(this)" class="text-red-500 hover:text-red-700 text-xs font-medium">
                Hapus
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Latitude <span class="text-red-500">*</span></label>
                <input type="number" step="any" name="pickup_points[${pickupPointCount-1}][lat]" 
                    placeholder="-6.8350" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-lat">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Longitude <span class="text-red-500">*</span></label>
                <input type="number" step="any" name="pickup_points[${pickupPointCount-1}][lng]" 
                    placeholder="108.2400" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-lng">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Nama Titik</label>
                <input type="text" name="pickup_points[${pickupPointCount-1}][nama]" 
                    placeholder="Contoh: Pasar Cigasong"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm pickup-nama">
            </div>
        </div>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div>
                <label class="block text-xs font-medium text-blue-800 mb-1">Provinsi</label>
                <input type="text" value="Jawa Barat" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                <input type="hidden" name="pickup_points[${pickupPointCount-1}][provinsi]" value="Jawa Barat">
            </div>
            <div>
                <label class="block text-xs font-medium text-blue-800 mb-1">Kabupaten</label>
                <input type="text" value="Majalengka" readonly class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-blue-100 text-blue-700 text-sm cursor-not-allowed">
                <input type="hidden" name="pickup_points[${pickupPointCount-1}][kabupaten]" value="Majalengka">
            </div>
            <div>
                <label class="block text-xs font-medium text-blue-800 mb-1">Kecamatan *</label>
                <select name="pickup_points[${pickupPointCount-1}][kecamatan]" required class="pickup-kecamatan w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white"
                    onchange="updatePickupDesa(this)">
                    <option value="">Pilih Kecamatan</option>
                    ${kecamatanOptions}
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-blue-800 mb-1">Desa *</label>
                <select name="pickup_points[${pickupPointCount-1}][desa]" required class="pickup-desa w-full px-3 py-2 rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-sm bg-white">
                    <option value="">Pilih Desa</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(div);
    calculateDistance();
}

function removePickupPoint(btn) {
    btn.closest('.pickup-point').remove();
    reindexPickupPoints();
    calculateDistance();
}

function reindexPickupPoints() {
    const points = document.querySelectorAll('.pickup-point');
    points.forEach((point, idx) => {
        point.dataset.index = idx;
        point.querySelector('.point-number').textContent = `Titik Pengangkutan ${idx + 1}`;
        const latInput = point.querySelector('.pickup-lat');
        const lngInput = point.querySelector('.pickup-lng');
        const namaInput = point.querySelector('.pickup-nama');
        const kecSelect = point.querySelector('.pickup-kecamatan');
        const desaSelect = point.querySelector('.pickup-desa');
        latInput.name = `pickup_points[${idx}][lat]`;
        lngInput.name = `pickup_points[${idx}][lng]`;
        if (namaInput) namaInput.name = `pickup_points[${idx}][nama]`;
        if (kecSelect) kecSelect.name = `pickup_points[${idx}][kecamatan]`;
        if (desaSelect) desaSelect.name = `pickup_points[${idx}][desa]`;
    });
    pickupPointCount = points.length;
}

function calculateDistance() {
    const startLat = parseFloat(document.querySelector('input[name="start_lat"]').value) || 0;
    const startLng = parseFloat(document.querySelector('input[name="start_lng"]').value) || 0;

    const latInputs = document.querySelectorAll('.pickup-lat');
    const lngInputs = document.querySelectorAll('.pickup-lng');

    const endLat = parseFloat(document.querySelector('input[name="end_lat"]').value) || 0;
    const endLng = parseFloat(document.querySelector('input[name="end_lng"]').value) || 0;

    let total = 0;
    let prevLat = startLat;
    let prevLng = startLng;
    const points = [[startLat, startLng]];

    for (let i = 0; i < latInputs.length; i++) {
        const lat = parseFloat(latInputs[i].value);
        const lng = parseFloat(lngInputs[i].value);
        if (lat && lng) {
            total += haversine(prevLat, prevLng, lat, lng);
            prevLat = lat;
            prevLng = lng;
            points.push([lat, lng]);
        }
    }

    if (endLat && endLng) {
        total += haversine(prevLat, prevLng, endLat, endLng);
        points.push([endLat, endLng]);
    }

    document.getElementById('totalJarakDisplay').textContent = total.toFixed(2) + ' km';
    updateMap(points);
}

function haversine(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function updateMap(points) {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    if (routeLine) map.removeLayer(routeLine);

    if (points.length < 1) return;

    points.forEach((point, i) => {
        let color, label;
        if (i === 0) {
            color = '#10b981';
            label = 'Start';
        } else if (i === points.length - 1) {
            color = '#334155';
            label = 'End';
        } else {
            color = '#3b82f6';
            label = `Pickup ${i}`;
        }

        const marker = L.circleMarker(point, {
            radius: 8,
            fillColor: color,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map).bindPopup(label);
        markers.push(marker);
    });

    if (points.length > 1) {
        routeLine = L.polyline(points, { color: '#10b981', weight: 4, opacity: 0.8 }).addTo(map);
    }

    if (points.length > 1) {
        map.fitBounds(points, { padding: [50, 50] });
    } else {
        map.setView(points[0], 13);
    }
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

    desaSelect.innerHTML = '<option value="">Pilih Desa</option>';

    desaList.forEach(desa => {
        const option = document.createElement('option');
        option.value = desa;
        option.textContent = desa;
        desaSelect.appendChild(option);
    });
}

function updatePickupDesa(kecamatanSelect) {
    const item = kecamatanSelect.closest('.pickup-point');
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

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    initDragAndDrop();

    // === RESTORE DESA OPTIONS UNTUK START ===
    @if(old('start_kecamatan', $wasteTrip->start_kecamatan))
        updateDesaOptions('start');
        document.getElementById('start_desa').value = @json(old('start_desa', $wasteTrip->start_desa));
    @endif

    // === RESTORE DESA OPTIONS UNTUK END ===
    @if(old('end_kecamatan', $wasteTrip->end_kecamatan))
        updateDesaOptions('end');
        document.getElementById('end_desa').value = @json(old('end_desa', $wasteTrip->end_desa));
    @endif

    // === RESTORE DESA OPTIONS UNTUK PICKUP POINTS ===
    @foreach($pickupPoints as $idx => $point)
        (function() {
            const items = document.querySelectorAll('.pickup-point');
            const item = items[{{ $idx }}];
            if (item) {
                const kecSelect = item.querySelector('.pickup-kecamatan');
                const desaSelect = item.querySelector('.pickup-desa');
                if (kecSelect && desaSelect) {
                    // Set kecamatan
                    kecSelect.value = @json(old('pickup_points.'.$idx.'.kecamatan', $point['kecamatan'] ?? ''));
                    // Trigger isi dropdown desa
                    updatePickupDesa(kecSelect);
                    // Set desa setelah dropdown terisi
                    setTimeout(() => {
                        desaSelect.value = @json(old('pickup_points.'.$idx.'.desa', $point['desa'] ?? ''));
                    }, 50);
                }
            }
        })();
    @endforeach
});
</script>

<style>
.pickup-point {
    transition: transform 0.2s, box-shadow 0.2s;
}

.pickup-point:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.pickup-point.dragging {
    opacity: 0.5;
    transform: scale(1.02);
}

.drag-handle {
    touch-action: none;
}
</style>
@endsection