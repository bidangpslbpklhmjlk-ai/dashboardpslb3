@php $v = $versi ?? null; @endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Version Code</label>
            <input type="number" name="version_code" min="1" value="{{ old('version_code', $v?->version_code) }}"
                   class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm" required>
            <p class="text-xs text-slate-400 mt-1">Angka bulat, harus lebih besar dari <code>versionCode</code> di build.gradle.kts versi terpasang.</p>
            @error('version_code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Version Name</label>
            <input type="text" name="version_name" maxlength="30" value="{{ old('version_name', $v?->version_name) }}"
                   placeholder="1.1"
                   class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm" required>
            @error('version_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Link Unduhan APK</label>
        <input type="text" name="download_url" maxlength="500" value="{{ old('download_url', $v?->download_url) }}"
               placeholder="https://drive.google.com/file/d/.../view"
               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm" required>
        <p class="text-xs text-slate-400 mt-1">Tempel link Bagikan Google Drive (akses "Siapa saja yang memiliki link"), otomatis diubah menjadi link unduh langsung.</p>
        @error('download_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Catatan Rilis</label>
        <textarea name="catatan" rows="3" maxlength="1000"
                  class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('catatan', $v?->catatan) }}</textarea>
        <p class="text-xs text-slate-400 mt-1">Ditampilkan di dialog pembaruan pada aplikasi.</p>
        @error('catatan')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="aktif" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
               @checked(old('aktif', $v?->aktif ?? true))>
        Aktif (dikirim ke aplikasi jika version code paling tinggi)
    </label>
</div>