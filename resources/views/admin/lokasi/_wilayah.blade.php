@php
    $kecamatanTerpilih = old('kecamatan', $lokasi->kecamatan ?? '');
    $desaTerpilih      = old('desa', $lokasi->desa ?? '');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="kecamatan" class="block text-sm font-medium text-slate-700 mb-1.5">Kecamatan <span class="text-red-500">*</span></label>
        <select name="kecamatan" id="kecamatan" required
            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
            <option value="">-- Pilih Kecamatan --</option>
            @foreach($kecamatanList as $kec)
                <option value="{{ $kec }}" {{ $kecamatanTerpilih === $kec ? 'selected' : '' }}>{{ $kec }}</option>
            @endforeach
        </select>
        @error('kecamatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="desa" class="block text-sm font-medium text-slate-700 mb-1.5">Desa/Kelurahan <span class="text-red-500">*</span></label>
        <select name="desa" id="desa" required
            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
            <option value="">-- Pilih Desa/Kelurahan --</option>
        </select>
        <script>
(function () {
    const desaData = @json($desaData);
    const desaAwal = @json($desaTerpilih);
    const kecEl    = document.getElementById('kecamatan');
    const desaEl   = document.getElementById('desa');

    function isiDesa(kecamatan, terpilih = '') {
        desaEl.innerHTML = '';
        desaEl.add(new Option(kecamatan ? '-- Pilih Desa/Kelurahan --' : '-- Pilih kecamatan dulu --', ''));
        (desaData[kecamatan] || []).forEach(function (d) {
            desaEl.add(new Option(d, d, false, d === terpilih));
        });
    }

    kecEl.addEventListener('change', function () { isiDesa(kecEl.value); });
    isiDesa(kecEl.value, desaAwal);
})();
</script>
        @error('desa')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

