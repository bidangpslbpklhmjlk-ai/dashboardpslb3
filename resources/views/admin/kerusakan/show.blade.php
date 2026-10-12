@extends('layouts.admin')
@section('content')
@php
    use App\Support\Label;
    $field = 'rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm w-full';
@endphp
<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('admin.kerusakan.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Daftar kerusakan</a>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ Label::of($kerusakan->armada) }} <span class="text-red-600 text-base font-semibold">· {{ strtoupper($kerusakan->status) }}</span></h1>
            <p class="text-sm text-slate-500">Sejak {{ $kerusakan->tanggal_mulai->format('d/m/Y') }}
                @if($kerusakan->tanggal_selesai) · s/d {{ $kerusakan->tanggal_selesai->format('d/m/Y') }}@endif
                @if($kerusakan->keterangan) · {{ $kerusakan->keterangan }}@endif</p>
        </div>
        @if($kerusakan->status === 'rusak')
            <form method="POST" action="{{ route('admin.kerusakan.selesai', $kerusakan) }}" onsubmit="return confirm('Armada sudah diperbaiki? Pengalihan masa depan akan dikembalikan ke armada ini.')">
                @csrf @method('PATCH')
                <button class="px-4 py-2 rounded-xl bg-green-700 text-white text-sm font-semibold hover:bg-green-800">✔ Armada sudah diperbaiki</button>
            </form>
        @endif
    </div>

    @include('admin.partials.konflik')

    {{-- ===== JADWAL TERDAMPAK ===== --}}
    <section class="space-y-3">
        <h2 class="font-bold text-slate-800">Jadwal terdampak <span class="text-slate-400 font-normal text-sm">(masih menempel di armada rusak)</span></h2>

        @forelse($grup as $key => $g)
            @php
                $lokasi = $lokasiMap[$g['rute_id']] ?? null;
                $rute = \App\Models\RuteAngkut::find($g['rute_id']);
                $awal = $g['tanggal'][0];
                $akhir = end($g['tanggal']);
            @endphp
            <form method="POST" action="{{ route('admin.kerusakan.alihkan', $kerusakan) }}" x-data="{ mode: 'rute' }"
                  class="bg-white rounded-2xl border border-amber-200 p-4 space-y-3">
                @csrf
                <input type="hidden" name="key" value="{{ $key }}">

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-semibold text-slate-800">{{ Label::of($rute) }} <span class="text-slate-400 font-normal">· {{ $g['mulai'] }}–{{ $g['selesai'] }}</span></p>
                        <p class="text-xs text-slate-500">{{ count($g['tanggal']) }} hari terdampak: {{ collect($g['tanggal'])->take(6)->map(fn($t) => \Carbon\Carbon::parse($t)->format('d/m'))->implode(', ') }}{{ count($g['tanggal']) > 6 ? ', …' : '' }}</p>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-1.5"><input type="radio" value="rute" x-model="mode" name="mode"> Alihkan seluruh rute</label>
                        <label class="flex items-center gap-1.5 {{ $lokasi === null ? 'opacity-40' : '' }}">
                            <input type="radio" value="lokasi" x-model="mode" name="mode" @disabled($lokasi === null)> Pecah per lokasi</label>
                    </div>
                </div>
                @if($lokasi === null)
                    <p class="text-xs text-slate-400">Mode per lokasi nonaktif: relasi rute→lokasi belum dikonfigurasi (lihat PenugasanService::lokasiRute).</p>
                @endif

                <div class="grid sm:grid-cols-3 gap-3">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Dari tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ $awal }}" class="{{ $field }}"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Sampai tanggal <span class="font-normal text-slate-400">(lalu kembali otomatis)</span></label>
                        <input type="date" name="tanggal_selesai" value="{{ $akhir }}" class="{{ $field }}"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Alasan</label>
                        <input name="alasan" value="{{ $kerusakan->keterangan ?: 'Armada rusak' }}" class="{{ $field }}"></div>
                </div>

                {{-- Mode rute --}}
                <div x-show="mode === 'rute'" class="grid sm:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Armada pengganti</label>
                        <select name="armada_pengganti_id" class="{{ $field }}" :disabled="mode !== 'rute'">
                            <option value="">— pilih —</option>
                            @foreach($armadaList as $a)<option value="{{ $a->id }}" @disabled(in_array($a->id, $rusakIds))>{{ Label::of($a) }}{{ in_array($a->id, $rusakIds) ? ' (rusak)' : '' }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Sopir pengganti <span class="font-normal text-slate-400">(kosong = tetap)</span></label>
                        <select name="pegawai_pengganti_id" class="{{ $field }}" :disabled="mode !== 'rute'">
                            <option value="">— tetap sopir asal —</option>
                            @foreach($pegawaiList as $p)<option value="{{ $p->id }}">{{ Label::of($p) }}</option>@endforeach
                        </select></div>
                </div>

                {{-- Mode lokasi --}}
                @if($lokasi !== null)
                <div x-show="mode === 'lokasi'" x-cloak class="space-y-3">
                    <p class="text-xs text-slate-500">Tentukan armada pengganti untuk <b>setiap</b> lokasi. Armada rusak tidak boleh menyisakan lokasi.</p>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($lokasi as $l)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-700 flex-1 truncate">{{ Label::of($l) }}</span>
                                <select name="alokasi[{{ $l->id }}]" class="rounded-lg border border-slate-200 px-2 py-1 text-sm" :disabled="mode !== 'lokasi'">
                                    <option value="">—</option>
                                    @foreach($armadaList as $a)<option value="{{ $a->id }}" @disabled(in_array($a->id, $rusakIds))>{{ Label::of($a) }}</option>@endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <details class="text-sm">
                        <summary class="cursor-pointer text-slate-500">Sopir untuk tiap armada pengganti (opsional)</summary>
                        <div class="grid sm:grid-cols-2 gap-2 mt-2">
                            @foreach($armadaList as $a)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-600 w-28 truncate">{{ Label::of($a) }}</span>
                                    <select name="pegawai[{{ $a->id }}]" class="flex-1 rounded-lg border border-slate-200 px-2 py-1 text-xs" :disabled="mode !== 'lokasi'">
                                        <option value="">— tetap sopir asal —</option>
                                        @foreach($pegawaiList as $p)<option value="{{ $p->id }}">{{ Label::of($p) }}</option>@endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
                @endif

                <div class="flex justify-end"><button class="px-5 py-2 rounded-xl bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600">Alihkan & beri flag</button></div>
            </form>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400">
                Tidak ada jadwal yang masih menempel di armada ini. 🎉
            </div>
        @endforelse
    </section>

    {{-- ===== SUDAH DIALIHKAN ===== --}}
    <section class="space-y-3">
        <h2 class="font-bold text-slate-800">Sudah dialihkan <span class="text-slate-400 font-normal text-sm">({{ $dialihkan->count() }} penugasan harian)</span></h2>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2 text-left">Tanggal</th><th class="px-4 py-2 text-left">Rute</th><th class="px-4 py-2 text-left">Armada pengganti</th><th class="px-4 py-2 text-left">Sopir</th><th class="px-4 py-2 text-left">Lokasi</th><th class="px-4 py-2 text-left">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($dialihkan as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ Label::of($r->rute) }}</td>
                        <td class="px-4 py-2"><span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">{{ Label::of($r->armada) }}</span></td>
                        <td class="px-4 py-2">{{ Label::of($r->pegawai) }}</td>
                        <td class="px-4 py-2 text-xs text-slate-500">{{ $r->lokasi->isEmpty() ? 'Seluruh rute' : $r->lokasi->map(fn($l) => Label::of($l))->implode(', ') }}</td>
                        <td class="px-4 py-2 text-xs uppercase text-slate-400">{{ $r->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada pengalihan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
