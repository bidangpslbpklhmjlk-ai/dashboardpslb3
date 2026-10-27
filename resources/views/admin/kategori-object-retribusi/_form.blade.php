@php
    $val = [
        'kelompok' => old('kelompok', $kategori->kelompok ?? ''),
        'kode'     => old('kode', $kategori->kode ?? ''),
        'nama'     => old('nama', $kategori->nama ?? ''),
        'tarif'    => old('tarif', $kategori->tarif ?? ''),
        'satuan'   => old('satuan', $kategori->satuan ?? ''),
    ];
    $input = 'w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm';

    $kelompokAda = $kelompokList->contains($val['kelompok']);
    $modeBaru    = $val['kelompok'] !== '' && ! $kelompokAda;
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelompok <span class="text-red-500">*</span></label>

    <select id="kelompok-pilih" required class="{{ $input }}">
        <option value="">Pilih kelompok</option>
        @foreach($kelompokList as $k)
            <option value="{{ $k }}" @selected($kelompokAda && $val['kelompok'] === $k)>{{ $k }}</option>
        @endforeach
        <option value="__baru__" @selected($modeBaru)>+ Tambah kelompok baru…</option>
    </select>

    <input type="text" id="kelompok-baru" maxlength="150"
        value="{{ $modeBaru ? $val['kelompok'] : '' }}"
        class="{{ $input }} mt-2 {{ $modeBaru ? '' : 'hidden' }}"
        placeholder="Ketik nama kelompok baru">

    {{-- nilai yang benar-benar dikirim ke server --}}
    <input type="hidden" id="kelompok-input" name="kelompok" value="{{ $val['kelompok'] }}">

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
    var pilih  = document.getElementById('kelompok-pilih');
    var baru   = document.getElementById('kelompok-baru');
    var hidden = document.getElementById('kelompok-input');

    function sync() {
        var isBaru = pilih.value === '__baru__';
        baru.classList.toggle('hidden', !isBaru);
        baru.required = isBaru;
        hidden.value = isBaru ? baru.value.trim() : pilih.value;
    }

    pilih.addEventListener('change', function () {
        sync();
        if (pilih.value === '__baru__') baru.focus();
    });
    baru.addEventListener('input', sync);

    sync();
})();
</script>