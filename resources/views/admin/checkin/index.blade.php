@extends('layouts.admin')

@section('content')
<style>
    .ck-head h2 { margin:0; font-size:20px; font-weight:700; color:#0f172a; }
    .ck-head p { margin:2px 0 16px; font-size:13px; color:#64748b; }

    .ck-ok, .ck-err { padding:10px 14px; border-radius:10px; margin-bottom:14px; font-size:14px; }
    .ck-ok { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    .ck-err { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

    /* Filter */
    .ck-filter { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:14px 16px; margin-bottom:20px; }
    .ck-filter label, .ck-form label { display:block; font-size:12px; font-weight:600; color:#64748b; }
    .ck-filter label br, .ck-form label br { display:none; }

    /* Input seragam */
    .ck-filter input, .ck-filter select,
    .ck-form input:not([type=checkbox]):not([type=file]):not([type=hidden]),
    .ck-form select {
        display:block; margin-top:4px; padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px;
        background:#fff; font-size:14px; color:#0f172a; box-sizing:border-box;
    }
    .ck-filter input:focus, .ck-filter select:focus, .ck-form input:focus, .ck-form select:focus {
        outline:2px solid #bbf7d0; border-color:#15803d;
    }
    .ck-form input[type=file] { display:block; margin-top:4px; font-size:13px; }

    /* Tombol */
    .ck-filter button, .ck-form button[type=submit] {
        background:#15803d; color:#fff; border:0; border-radius:8px; padding:9px 16px;
        font-size:14px; font-weight:600; cursor:pointer;
    }
    .ck-filter button:hover, .ck-form button[type=submit]:hover { background:#166534; }
    .ck-form button[type=button] {
        background:#f1f5f9; color:#334155; border:1px solid #e2e8f0; border-radius:8px;
        padding:6px 10px; font-size:13px; cursor:pointer;
    }
    .ck-form button[type=button]:hover { background:#e2e8f0; }
    .ck-link-danger { background:none; border:0; padding:0; margin-top:6px; font-size:13px; font-weight:600; color:#b91c1c; cursor:pointer; }
    .ck-link-danger:hover { text-decoration:underline; }

    /* Rute & lokasi */
    .ck-rute { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:16px; margin-bottom:24px; }
    .ck-rute > h3 { margin:0 0 12px; font-size:15px; font-weight:700; color:#0f172a; }
    .ck-lok { border:1px solid #e2e8f0; border-radius:12px; background:#fff; padding:12px 14px; margin-bottom:10px; }
    .ck-lok-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .ck-lok-head strong { font-size:14px; color:#0f172a; }
    .ck-no { width:24px; height:24px; flex-shrink:0; border-radius:9999px; background:#15803d; color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; }
    .ck-badge { margin-left:auto; font-size:11px; font-weight:600; padding:2px 10px; border-radius:9999px; }
    .ck-belum { background:#f1f5f9; color:#64748b; }
    .ck-sudah { background:#dcfce7; color:#166534; }

    /* Item check-in */
    .ck-item { border-top:1px solid #f1f5f9; margin-top:10px; padding-top:10px; font-size:13px; color:#334155; line-height:1.5; }
    .ck-fotos { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
    .ck-foto { width:64px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0; }

    /* Detail (Revisi / + Check-in) */
    details > summary { cursor:pointer; display:inline-block; margin-top:8px; font-size:13px; font-weight:600; color:#15803d; list-style:none; }
    details > summary::-webkit-details-marker { display:none; }
    details > summary:hover { text-decoration:underline; }
    details[open] > summary { color:#64748b; }
    details > .ck-form { margin-top:8px; padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; }

    /* Form */
    .ck-form { display:grid; gap:12px; max-width:520px; }
    .ck-row { display:flex; gap:6px; align-items:center; margin-bottom:6px; }
    .ck-row select { flex:1; min-width:0; }
    .ck-row select, .ck-row input { margin-top:0 !important; }
    .ck-row button[type=button] { color:#b91c1c; }
    .ck-sub { font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px; }
    .ck-foto-hapus { font-size:12px; color:#64748b; }
    .ck-form .ck-foto-hapus label { display:inline-block; margin:6px 8px 0 0; }
</style>

<div class="ck-head">
    <h2>Check-in Lokasi Angkut</h2>
    <p>Catat sampah yang diangkut di tiap lokasi, per armada dan tanggal.</p>
</div>

@if (session('success')) <div class="ck-ok">{{ session('success') }}</div> @endif
@if ($errors->any())
    <div class="ck-err"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="GET" class="ck-filter">
    <label>Tanggal<br><input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" max="{{ today()->toDateString() }}"></label>
    <label>Armada<br>
        <select name="armada">
            @foreach ($armadaList as $a)
                <option value="{{ $a->id }}" @selected($a->id == $armadaId)>{{ $a->nomor_polisi }} - {{ $a->nama }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit">Tampilkan</button>
</form>

@forelse ($blok as $b)
    <div class="ck-rute">
        <h3>{{ \App\Support\Label::of($b['rute']) }}</h3>

        @foreach ($b['lokasi'] as $i => $l)
<div class="ck-lok">
    <div class="ck-lok-head">
        <span class="ck-no">{{ $i + 1 }}</span>
        <strong>{{ \App\Support\Label::of($l['lokasi']) }}</strong>
        @if ($l['checkins']->isEmpty())
            <span class="ck-badge ck-belum">Belum check-in</span>
        @else
            <span class="ck-badge ck-sudah">{{ $l['checkins']->count() }} check-in</span>
        @endif
    </div>

                @foreach ($l['checkins'] as $c)
                    <div class="ck-item">
                        <div>
                            {{ $c->waktu_kejadian->format('H:i') }} &middot; ritasi {{ $c->ritasi_ke }}
                            &middot; <strong>{{ rtrim(rtrim(number_format($c->sampah->sum('berat_kg'), 2, ',', '.'), '0'), ',') }} kg</strong>
                            &middot; {{ $c->sumber === 'app' ? 'Aplikasi' : 'Admin' }}
                            @if (!empty($c->flag['direvisi_admin'])) <em>(direvisi)</em> @endif
                        </div>
                        <div>
                            @foreach ($c->sampah as $s)
                                {{ \App\Support\Label::of($s->jenisSampah) }}: {{ $s->berat_kg }} kg{{ $loop->last ? '' : ',' }}
                            @endforeach
                        </div>
                        @if ($c->catatan) <div><small>{{ $c->catatan }}</small></div> @endif
<div class="ck-fotos">
    @foreach ($c->fotos as $f)
                                <a href="{{ asset('storage/' . $f->path) }}" target="_blank"><img class="ck-foto" src="{{ asset('storage/' . $f->path) }}" alt="foto"></a>
                            @endforeach
                        </div>

                        <details>
                            <summary>Revisi</summary>
                            @include('admin.checkin._form', ['c' => $c, 'ruteId' => $b['rute']->id, 'lokasiId' => $l['lokasi']->id])
                        </details>
                        <form method="POST" action="{{ route('admin.checkin.destroy', $c) }}" onsubmit="return confirm('Hapus check-in ini?')" style="margin-top:4px">
                            @csrf @method('DELETE')
                            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                            <input type="hidden" name="armada" value="{{ $armadaId }}">
                            <button type="submit" class="ck-link-danger">Hapus</button>
                        </form>
                    </div>
                @endforeach

                <details>
                    <summary>+ Check-in</summary>
                    @include('admin.checkin._form', ['c' => null, 'ruteId' => $b['rute']->id, 'lokasiId' => $l['lokasi']->id])
                </details>
            </div>
        @endforeach
    </div>
@empty
    <p style="color:#9ca3af">Tidak ada jadwal angkut untuk armada dan tanggal ini.</p>
@endforelse

<script>
function ckTambah(btn) {
    var f = btn.closest('form'), box = f.querySelector('.ck-rows'), tpl = f.querySelector('template');
    var idx = parseInt(box.dataset.next || '0', 10);
    box.dataset.next = idx + 1;
    box.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__IDX__/g, idx));
}
function ckHapusBaris(btn) {
    var row = btn.closest('.ck-row'), box = row.parentNode;
    if (box.querySelectorAll('.ck-row').length > 1) row.remove();
}
</script>
@endsection