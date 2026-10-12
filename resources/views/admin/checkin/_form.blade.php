@php
    $edit = $c !== null;
    $baris = $edit
        ? $c->sampah->map(fn ($s) => ['j' => $s->jenis_sampah_id, 'b' => $s->berat_kg])->values()->all()
        : [['j' => '', 'b' => '']];
    $waktu = $edit
        ? $c->waktu_kejadian->format('Y-m-d\TH:i')
        : ($tanggal->isToday() ? now()->format('Y-m-d\TH:i') : $tanggal->format('Y-m-d') . 'T08:00');
@endphp

<form method="POST" enctype="multipart/form-data" class="ck-form"
      action="{{ $edit ? route('admin.checkin.update', $c) : route('admin.checkin.store') }}">
    @csrf
    @if ($edit) @method('PUT') @endif
    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
    <input type="hidden" name="armada" value="{{ $armadaId }}">
    @unless ($edit)
        <input type="hidden" name="rute_id" value="{{ $ruteId }}">
        <input type="hidden" name="lokasi_angkut_id" value="{{ $lokasiId }}">
    @endunless

    <label>Waktu check-in<br><input type="datetime-local" name="waktu_kejadian" value="{{ $waktu }}" required></label>
    <label>Ritasi ke<br><input type="number" name="ritasi_ke" min="1" max="20" value="{{ $edit ? $c->ritasi_ke : 1 }}" required style="width:80px"></label>

    <div>
        <div style="font-size:13px;margin-bottom:4px">Jenis &amp; berat sampah</div>
        <div class="ck-rows" data-next="{{ count($baris) }}">
            @foreach ($baris as $i => $r)
                <div class="ck-row">
                    <select name="sampah[{{ $i }}][jenis_sampah_id]" required>
                        <option value="">- jenis -</option>
                        @foreach ($jenisList as $j)
                            <option value="{{ $j->id }}" @selected($r['j'] == $j->id)>{{ \App\Support\Label::of($j) }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0.01" name="sampah[{{ $i }}][berat_kg]" value="{{ $r['b'] }}" placeholder="kg" required style="width:100px">
                    <button type="button" onclick="ckHapusBaris(this)">&times;</button>
                </div>
            @endforeach
        </div>
        <template>
            <div class="ck-row">
                <select name="sampah[__IDX__][jenis_sampah_id]" required>
                    <option value="">- jenis -</option>
                    @foreach ($jenisList as $j)
                        <option value="{{ $j->id }}">{{ \App\Support\Label::of($j) }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" min="0.01" name="sampah[__IDX__][berat_kg]" placeholder="kg" required style="width:100px">
                <button type="button" onclick="ckHapusBaris(this)">&times;</button>
            </div>
        </template>
        <button type="button" onclick="ckTambah(this)" style="margin-top:4px">+ Jenis sampah</button>
    </div>

    @if ($edit && $c->fotos->isNotEmpty())
        <div style="font-size:13px">Centang untuk menghapus foto:
            @foreach ($c->fotos as $f)
                <label style="margin-right:6px"><input type="checkbox" name="hapus_foto[]" value="{{ $f->id }}">
                    <img class="ck-foto" src="{{ asset('storage/' . $f->path) }}" alt="foto"></label>
            @endforeach
        </div>
    @endif

    <label>Foto{{ $edit ? ' (tambahan)' : '' }}<br><input type="file" name="foto[]" accept="image/*" multiple capture="environment"></label>
    <label>Catatan<br><input type="text" name="catatan" maxlength="500" value="{{ $edit ? $c->catatan : '' }}" style="width:100%"></label>

    @if ($edit)
        <label>Alasan revisi (wajib)<br><input type="text" name="alasan_manual" maxlength="255" required style="width:100%"></label>
    @endif

    <button type="submit">{{ $edit ? 'Simpan revisi' : 'Simpan check-in' }}</button>
</form>