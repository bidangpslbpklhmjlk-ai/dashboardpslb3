@extends('layouts.admin')

@section('content')
<style>
    .pj-armada { margin-bottom:28px; }
    .pj-armada h3 { margin:0 0 10px; }
    .pj-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:12px; }
    .pj-hari { border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:12px; }
    .pj-hari h4 { margin:0 0 8px; font-size:15px; }
    .pj-rute { font-weight:600; font-size:13px; margin-top:8px; }
    .pj-hari ol { margin:4px 0 0; padding-left:18px; font-size:13px; }
    .pj-kosong { color:#9ca3af; font-size:13px; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h2 style="margin:0">Jadwal Armada</h2>
    <a href="{{ route('admin.kerusakan.index') }}">Armada rusak</a>
</div>

@forelse ($armada as $a)
    <div class="pj-armada">
        <h3>{{ $a['nopol'] }} <small style="font-weight:400">- {{ $a['nama'] }}</small></h3>

        <div class="pj-grid">
            @foreach ($namaHari as $no => $nama)
                <div class="pj-hari">
                    <h4>{{ $nama }}</h4>

                    @forelse ($a['hari'][$no] as $it)
                        <div class="pj-rute">{{ $it['rute'] }}</div>
                        @if ($it['lokasi'])
                            <ol>
                                @foreach ($it['lokasi'] as $l)
                                    <li>{{ $l }}</li>
                                @endforeach
                            </ol>
                        @else
                            <div class="pj-kosong">Belum ada lokasi.</div>
                        @endif
                    @empty
                        <div class="pj-kosong">Tidak ada jadwal.</div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
@empty
    <p class="pj-kosong">Belum ada armada.</p>
@endforelse
@endsection