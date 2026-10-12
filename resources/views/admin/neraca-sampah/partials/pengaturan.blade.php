<form action="{{ route('admin.neraca-sampah.update', $laporan) }}" method="POST"
      class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
    @csrf
    @method('PUT')

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $input = 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500';
        $label = 'block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide';
    @endphp

    {{-- Umum --}}
    <div>
        <h3 class="font-semibold text-slate-800 mb-3">Umum</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="{{ $label }}">Industri</label>
                <select name="industri_id" class="{{ $input }}" required>
                    @foreach($industriList as $i)
                        <option value="{{ $i->id }}" @selected(old('industri_id', $laporan->industri_id) == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Periode Mulai</label>
                <input type="date" name="periode_mulai" class="{{ $input }}" required
                       value="{{ old('periode_mulai', $laporan->periode_mulai->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="{{ $label }}">Periode Selesai</label>
                <input type="date" name="periode_selesai" class="{{ $input }}" required
                       value="{{ old('periode_selesai', $laporan->periode_selesai->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="{{ $label }}">Status</label>
                <select name="status" class="{{ $input }}">
                    <option value="draft" @selected(old('status', $laporan->status) === 'draft')>Draft</option>
                    <option value="final" @selected(old('status', $laporan->status) === 'final')>Final</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tempat & tanggal --}}
    <div>
        <h3 class="font-semibold text-slate-800 mb-3">Tempat & Tanggal Tanda Tangan</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Kota</label>
                <input type="text" name="kota_ttd" class="{{ $input }}" value="{{ old('kota_ttd', $laporan->kota_ttd) }}">
            </div>
            <div>
                <label class="{{ $label }}">Tanggal</label>
                <input type="date" name="tanggal_ttd" class="{{ $input }}"
                       value="{{ old('tanggal_ttd', optional($laporan->tanggal_ttd)->format('Y-m-d')) }}">
            </div>
        </div>
    </div>

    {{-- Penandatangan --}}
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold text-slate-800 mb-3">Mengetahui</h3>
            <div class="space-y-3">
                <div><label class="{{ $label }}">Jabatan</label>
                    <input type="text" name="mengetahui_jabatan" class="{{ $input }}" value="{{ old('mengetahui_jabatan', $laporan->mengetahui_jabatan) }}"></div>
                <div><label class="{{ $label }}">Nama</label>
                    <input type="text" name="mengetahui_nama" class="{{ $input }}" value="{{ old('mengetahui_nama', $laporan->mengetahui_nama) }}"></div>
                <div><label class="{{ $label }}">NIP / NIK</label>
                    <input type="text" name="mengetahui_nip" class="{{ $input }}" value="{{ old('mengetahui_nip', $laporan->mengetahui_nip) }}"></div>
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-slate-800 mb-3">Pembuat Laporan</h3>
            <div class="space-y-3">
                <div><label class="{{ $label }}">Jabatan</label>
                    <input type="text" name="pembuat_jabatan" class="{{ $input }}" value="{{ old('pembuat_jabatan', $laporan->pembuat_jabatan) }}"></div>
                <div><label class="{{ $label }}">Nama</label>
                    <input type="text" name="pembuat_nama" class="{{ $input }}" value="{{ old('pembuat_nama', $laporan->pembuat_nama) }}"></div>
                <div><label class="{{ $label }}">NIP / NIK</label>
                    <input type="text" name="pembuat_nip" class="{{ $input }}" value="{{ old('pembuat_nip', $laporan->pembuat_nip) }}"></div>
            </div>
        </div>
    </div>

    <div>
        <label class="{{ $label }}">Catatan</label>
        <textarea name="catatan" rows="2" class="{{ $input }}">{{ old('catatan', $laporan->catatan) }}</textarea>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Simpan Perubahan</button>
    </div>
</form>