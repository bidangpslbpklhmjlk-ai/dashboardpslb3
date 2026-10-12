<div class="px-5 py-6 border-t border-slate-200 grid grid-cols-2 gap-8 text-sm text-slate-700">
    <div>
        <p>Mengetahui,</p>
        <p>{{ $laporan->mengetahui_jabatan ?: '-' }}</p>
        <div class="h-16"></div>
        <p class="font-semibold">{{ $laporan->mengetahui_nama ?: '-' }}</p>
        <p class="text-xs text-slate-500">{{ $laporan->mengetahui_nip }}</p>
    </div>
    <div class="text-right">
        <p>{{ $laporan->kota_ttd ?: '-' }}{{ $laporan->tanggal_ttd ? ', ' . $laporan->tanggal_ttd->translatedFormat('d F Y') : '' }}</p>
        <p>{{ $laporan->pembuat_jabatan ?: '-' }}</p>
        <div class="h-16"></div>
        <p class="font-semibold">{{ $laporan->pembuat_nama ?: '-' }}</p>
        <p class="text-xs text-slate-500">{{ $laporan->pembuat_nip }}</p>
    </div>
</div>