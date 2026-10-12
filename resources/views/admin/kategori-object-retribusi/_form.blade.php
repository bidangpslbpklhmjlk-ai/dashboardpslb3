@php
    $val = [
        'kelompok' => old('kelompok', $kategori->kelompok ?? ''),
        'kode'     => old('kode', $kategori->kode ?? ''),
        'nama'     => old('nama', $kategori->nama ?? ''),
        'tarif'    => old('tarif', $kategori->tarif ?? ''),
        'satuan'   => old('satuan', $kategori->satuan ?? ''),
    ];
    $input = 'w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm';
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelompok <span class="text-red-500">*</span></label>
    <select id="kelompok-pilih" class="{{ $input }} mb-2">
        <option value="">Pilih kelompok yang sudah ada</option>
        @foreach($kelompokList as $k)
            <option value="{{ $k }}" @selected($val['kelompok'] === $k)>{{ $k }}</option>
        @endforeach
    </select>
    <input type="text" id="kelompok-input" name="kelompok" value="{{ $val['kelompok'] }}" required maxlength="150"
        class="{{ $input }}" placeholder="atau ketik nama kelompok baru">
    @error('kelompok')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode <span class="text-red-500">*</span></label>
        <input type="text" name="kode" value="{{ $val['kode'] }}" required maxlength="20" class="{{ $input }}" placeholder="2.a">
        @error('kode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ $val['nama'] }}" required maxlength="150" class="{{ $input }}" placeholder="Contoh: Bintang III">
        @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tarif (Rp) <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0" name="tarif" value="{{ $val['tarif'] }}" required class="{{ $input }}">
        @error('tarif')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Satuan <span class="text-red-500">*</span></label>
        <select name="satuan" required class="{{ $input }}">
            <option value="">Pilih satuan</option>
            @foreach(\App\Models\KategoriObjectRetribusi::SATUAN as $key => $label)
                <option value="{{ $key }}" @selected($val['satuan'] === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('satuan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<p class="text-xs text-slate-400">
    Tarif berlaku per satuan yang dipilih. Untuk object retribusi <strong>kolektif</strong>
    (RW/Pasar/Terminal), tagihan dihitung otomatis: tarif × jumlah unit.
</p>
<script>
(function () {
    var pilih = document.getElementById('kelompok-pilih');
    var input = document.getElementById('kelompok-input');

    pilih.addEventListener('change', function () {
        if (pilih.value) input.value = pilih.value;
    });

    input.addEventListener('input', function () {
        var cocok = Array.from(pilih.options).some(function (o) { return o.value === input.value; });
        pilih.value = cocok ? input.value : '';
    });
})();
</script>