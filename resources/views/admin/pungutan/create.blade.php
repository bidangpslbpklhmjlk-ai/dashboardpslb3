@extends('layouts.admin')
@section('title', 'Catat Kunjungan')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.pungutan.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Catat Kunjungan</h1>
            <p class="text-slate-500 mt-1">Catat kunjungan petugas ke object retribusi & terbitkan invoice</p>
        </div>
    </div>

    <form action="{{ route('admin.pungutan.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Object Retribusi <span class="text-red-500">*</span></label>
            <select name="object_retribusi_id" id="objectSelect" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                <option value="">Pilih object retribusi</option>
                @foreach($objectRetribusiList as $w)
@php
    $kolektif = (bool) $w->is_kolektif;
    $unit = $kolektif ? max(1, (int) $w->jumlah_unit) : 1;
    $tarif = (int) ($w->kategori->tarif ?? 0);
    $satuan = $w->kategori->satuan ?? '';
@endphp
<option value="{{ $w->id }}"
        data-tarif="{{ $tarif }}"
        data-unit="{{ $unit }}"
        data-kolektif="{{ $kolektif ? 1 : 0 }}"
        data-satuan="{{ $satuan }}"
        {{ old('object_retribusi_id') == $w->id ? 'selected' : '' }}>
    {{ $w->nama }} — {{ $w->kategori->nama ?? '-' }}
    @if($kolektif)
        ({{ $unit }} unit × Rp {{ number_format($tarif, 0, ',', '.') }} {{ $satuan }})
    @else
        (Rp {{ number_format($tarif, 0, ',', '.') }} {{ $satuan }})
    @endif
</option>
                @endforeach
            </select>
            @error('object_retribusi_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

            <div id="infoTagihan" class="hidden mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm">
                <div class="flex justify-between py-0.5">
                    <span class="text-slate-600">Jumlah unit</span>
                    <span id="infoUnit" class="font-medium text-slate-900"></span>
                </div>
                <div class="flex justify-between py-0.5">
                    <span class="text-slate-600">Tarif per unit</span>
                    <span id="infoTarif" class="font-medium text-slate-900"></span>
                </div>
                <div class="flex justify-between py-0.5">
    <span class="text-slate-600">Satuan tarif</span>
    <span id="infoSatuan" class="font-medium text-slate-900"></span>
</div>
                <div class="flex justify-between pt-2 mt-2 border-t border-emerald-200">
                    <span class="font-semibold text-slate-700">Total seharusnya dibayar <span id="infoSatuanTotal" class="font-normal text-slate-500"></span></span>
                    <span id="infoTotal" class="font-bold text-emerald-700"></span>
                </div>
            </div>

            @if($objectRetribusiList->isEmpty())
                <p class="text-amber-600 text-xs mt-1">Belum ada object retribusi aktif terdaftar. <a href="{{ route('admin.object-retribusi.create') }}" class="underline">Daftarkan object retribusi</a> terlebih dahulu.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Kunjungan <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('tanggal_kunjungan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Bukti Kunjungan</label>
            <input type="file" name="foto" accept="image/*"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-slate-100 file:text-slate-700 file:text-xs">
            <p class="text-xs text-slate-400 mt-1">Maks. 2MB, format gambar.</p>
            @error('foto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
            <textarea name="catatan" rows="2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm resize-none">{{ old('catatan') }}</textarea>
            @error('catatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="statusKunjunganWrap">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Kunjungan <span class="text-red-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1 justify-center">
                    <input type="radio" name="status" value="selesai" onchange="toggleStatusBayar()" {{ old('status', 'selesai') == 'selesai' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-slate-700 font-medium">Selesai</span>
                </label>
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 flex-1 justify-center">
                    <input type="radio" name="status" value="batal" onchange="toggleStatusBayar()" {{ old('status') == 'batal' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                    <span class="text-sm text-slate-700 font-medium">Batal</span>
                </label>
            </div>
            @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Invoice hanya diterbitkan otomatis untuk kunjungan berstatus "Selesai".</p>
        </div>

        <div id="nominalWrap">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nominal Diterima (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="nominal" id="nominalInput" min="0" step="1" value="{{ old('nominal') }}"
                   class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <div class="flex items-center justify-between mt-1">
                <p id="nominalSelisih" class="text-xs text-slate-400"></p>
                <button type="button" onclick="isiNominal(true)" class="text-xs text-emerald-700 underline">Samakan dengan tagihan</button>
            </div>
            @error('nominal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="statusBayarWrap">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Pembayaran <span class="text-red-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1 justify-center">
                    <input type="radio" name="status_pembayaran" value="lunas" {{ old('status_pembayaran') == 'lunas' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-slate-700 font-medium">Lunas</span>
                </label>
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 flex-1 justify-center">
                    <input type="radio" name="status_pembayaran" value="belum_lunas" {{ old('status_pembayaran', 'belum_lunas') == 'belum_lunas' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <span class="text-sm text-slate-700 font-medium">Belum Lunas</span>
                </label>
            </div>
            @error('status_pembayaran')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Kalau dipilih "Lunas", invoice yang terbit otomatis ditandai lunas.</p>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.pungutan.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Simpan
            </button>
        </div>
    </form>
</div>

<script>
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function toggleStatusBayar() {
    const selesai = document.querySelector('input[name="status"][value="selesai"]').checked;
    document.getElementById('statusBayarWrap').style.display = selesai ? '' : 'none';
    document.getElementById('nominalWrap').style.display = selesai ? '' : 'none';
}

function dataObject() {
    const opt = document.getElementById('objectSelect').selectedOptions[0];
    if (!opt || !opt.value) return null;
    const tarif = parseInt(opt.dataset.tarif || 0);
    const unit = parseInt(opt.dataset.unit || 1);
    const satuan = (opt.dataset.satuan || '').trim();
    return { tarif, unit, satuan, kolektif: opt.dataset.kolektif === '1', total: tarif * unit };
}

function isiNominal(paksa) {
    const d = dataObject();
    const panel = document.getElementById('infoTagihan');
    const input = document.getElementById('nominalInput');
    if (!d) { panel.classList.add('hidden'); hitungSelisih(); return; }

    document.getElementById('infoUnit').textContent = d.kolektif ? d.unit.toLocaleString('id-ID') + ' unit' : '1 (non-kolektif)';
    document.getElementById('infoTarif').textContent = rp(d.tarif);
    document.getElementById('infoTotal').textContent = rp(d.total);
    document.getElementById('infoSatuan').textContent = d.satuan || '-';
    document.getElementById('infoSatuanTotal').textContent = d.satuan ? '(' + d.satuan + ')' : '';
    panel.classList.remove('hidden');

    if (paksa || input.value === '') input.value = d.total;
    hitungSelisih();
}

function hitungSelisih() {
    const d = dataObject();
    const el = document.getElementById('nominalSelisih');
    const v = document.getElementById('nominalInput').value;
    if (!d || v === '') { el.textContent = ''; return; }
    const selisih = parseInt(v) - d.total;
    if (selisih === 0) { el.textContent = 'Sesuai tagihan.'; el.className = 'text-xs text-emerald-600'; }
    else if (selisih < 0) { el.textContent = 'Kurang ' + rp(-selisih) + ' dari tagihan.'; el.className = 'text-xs text-amber-600'; }
    else { el.textContent = 'Lebih ' + rp(selisih) + ' dari tagihan.'; el.className = 'text-xs text-amber-600'; }
}

document.getElementById('objectSelect').addEventListener('change', () => isiNominal(true));
document.getElementById('nominalInput').addEventListener('input', hitungSelisih);
document.addEventListener('DOMContentLoaded', () => { toggleStatusBayar(); isiNominal(false); });
</script>
@endsection