{{--
    Form bersama untuk create dan edit rute pengangkutan.
    Variabel: $rute (null saat create), $action, $method, $armadas, $hariList,
              $lokasiData, $selectedIds, $kecamatanList
--}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
            <p class="font-semibold mb-1">Data belum bisa disimpan:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi rute --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-800 mb-4">Informasi Rute</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="armada_id" class="block text-xs font-semibold text-slate-500 mb-1">Armada <span class="text-red-500">*</span></label>
                <select id="armada_id" name="armada_id" required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">Pilih armada</option>
                    @foreach($armadas as $a)
                        <option value="{{ $a->id }}" @selected(old('armada_id', $rute->armada_id ?? null) == $a->id)>
                            {{ $a->nomor_polisi }}{{ $a->jenis_kendaraan ? ' — ' . $a->jenis_kendaraan : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="hari" class="block text-xs font-semibold text-slate-500 mb-1">Hari <span class="text-red-500">*</span></label>
                <select id="hari" name="hari" required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">Pilih hari</option>
                    @foreach($hariList as $h)
                        <option value="{{ $h }}" @selected(old('hari', $rute->hari ?? null) === $h)>{{ $h }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-500 mb-1">Nama rute <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input id="nama" type="text" name="nama" maxlength="100"
                       value="{{ old('nama', $rute->nama ?? '') }}"
                       placeholder="mis. Rute Pasar Pagi"
                       class="w-full rounded-lg border-slate-300 text-sm focus:border-green-600 focus:ring-green-600">
            </div>

            <div class="flex items-end">
                <input type="hidden" name="aktif" value="0">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 pb-2">
                    <input type="checkbox" name="aktif" value="1"
                           @checked(old('aktif', $rute->aktif ?? true))
                           class="rounded border-slate-300 text-green-700 focus:ring-green-600">
                    Rute aktif
                </label>
            </div>
        </div>
    </div>

    {{-- Lokasi --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-800 mb-3">Pilih Lokasi Pengambilan</h2>
            <div class="flex flex-wrap gap-2 mb-3">
                <input id="lokasi-search" type="search" placeholder="Cari nama, desa, atau kecamatan"
                       class="flex-1 min-w-[180px] rounded-lg border-slate-300 text-sm focus:border-green-600 focus:ring-green-600">
                <select id="lokasi-kecamatan"
                        class="rounded-lg border-slate-300 text-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">Semua kecamatan</option>
                    @foreach($kecamatanList as $kec)
                        <option value="{{ $kec }}">{{ $kec }}</option>
                    @endforeach
                </select>
            </div>
            <div id="available-list" class="max-h-80 overflow-y-auto rounded-lg border border-slate-200"></div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-800">Urutan Rute</h2>
            <p class="text-xs text-slate-500 mb-3">Tarik <span class="font-semibold">⋮⋮</span> untuk mengubah urutan. Rute dilalui dari nomor 1 ke bawah.</p>
            <ol id="selected-list" class="space-y-2"></ol>
            <p id="selected-empty" class="rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-400">
                Belum ada lokasi. Klik lokasi di sebelah kiri untuk menambahkan.
            </p>
            @error('lokasi_ids')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Pratinjau peta --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-800">Pratinjau Rute</h2>
            <p class="text-sm text-slate-600">Jarak: <span id="distance" class="font-semibold text-slate-900">-</span></p>
        </div>
        <div id="preview-map" class="h-[380px] w-full z-0"></div>
        <p class="px-4 py-2 text-xs text-slate-400">Jarak dan garis rute dihitung otomatis dari urutan lokasi, lalu dihitung ulang dan disimpan saat Anda menekan Simpan.</p>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.rute-angkut.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-green-700 hover:bg-green-800 text-white text-sm font-semibold shadow-sm transition-colors">
            Simpan Rute
        </button>
    </div>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const LIST = @json($lokasiData);
    const BY_ID = Object.fromEntries(LIST.map((l) => [l.id, l]));
    let selected = @json($selectedIds).map(Number).filter((id) => BY_ID[id]);

    const availEl = document.getElementById('available-list');
    const selEl = document.getElementById('selected-list');
    const emptyEl = document.getElementById('selected-empty');
    const searchEl = document.getElementById('lokasi-search');
    const kecEl = document.getElementById('lokasi-kecamatan');
    const distEl = document.getElementById('distance');

    const el = (tag, cls, text) => {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text !== undefined) e.textContent = text;
        return e;
    };
    const wilayah = (l) => [l.desa, l.kecamatan].filter(Boolean).join(', ') || 'Wilayah belum diisi';

    function renderAvailable() {
        const q = searchEl.value.trim().toLowerCase();
        const kec = kecEl.value;
        availEl.replaceChildren();

        const items = LIST.filter((l) =>
            !selected.includes(l.id) &&
            (!kec || l.kecamatan === kec) &&
            (!q || (l.nama + ' ' + (l.desa || '') + ' ' + (l.kecamatan || '')).toLowerCase().includes(q))
        );

        if (!items.length) {
            availEl.append(el('p', 'p-4 text-sm text-center text-slate-400', 'Tidak ada lokasi yang cocok.'));
            return;
        }

        items.forEach((l) => {
            const b = el('button', 'w-full text-left px-3 py-2 flex items-center justify-between gap-3 border-b border-slate-100 last:border-0 hover:bg-green-50');
            b.type = 'button';
            const left = el('div');
            left.append(el('p', 'text-sm font-medium text-slate-800', l.nama), el('p', 'text-xs text-slate-500', wilayah(l)));
            b.append(left, el('span', 'text-xs font-semibold text-green-700 whitespace-nowrap', '+ Tambah'));
            b.addEventListener('click', () => { selected.push(l.id); refresh(); });
            availEl.append(b);
        });
    }

    function renderSelected() {
        selEl.replaceChildren();
        emptyEl.classList.toggle('hidden', selected.length > 0);

        selected.forEach((id, i) => {
            const l = BY_ID[id];
            const li = el('li', 'flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-3 py-2');
            li.dataset.id = id;

            const handle = el('span', 'drag-handle cursor-grab select-none text-slate-400 text-lg leading-none', '⋮⋮');
            const num = el('span', 'w-6 h-6 shrink-0 rounded-full bg-green-700 text-white text-xs font-bold flex items-center justify-center', String(i + 1));
            const info = el('div', 'flex-1 min-w-0');
            info.append(el('p', 'text-sm font-medium text-slate-800 truncate', l.nama), el('p', 'text-xs text-slate-500 truncate', wilayah(l)));

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lokasi_ids[]';
            input.value = id;

            const rm = el('button', 'text-slate-400 hover:text-red-600 text-sm px-1', '✕');
            rm.type = 'button';
            rm.title = 'Hapus dari rute';
            rm.addEventListener('click', () => { selected = selected.filter((x) => x !== id); refresh(); });

            li.append(handle, num, info, input, rm);
            selEl.append(li);
        });
    }

    // --- Peta pratinjau ---
    const map = L.map('preview-map').setView([-6.8366, 108.2274], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    const layer = L.featureGroup().addTo(map);
    let timer = null;
    let reqId = 0;

    const numIcon = (n) => L.divIcon({
        className: '',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        html: '<div style="width:24px;height:24px;border-radius:9999px;background:#15803d;color:#fff;font:700 12px/24px sans-serif;text-align:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35)">' + n + '</div>'
    });

    function fit() {
        if (layer.getLayers().length) map.fitBounds(layer.getBounds().pad(0.15));
    }

    async function drawMap() {
        layer.clearLayers();
        const pts = selected.map((id) => BY_ID[id]);

        pts.forEach((l, i) => L.marker([l.lat, l.lng], { icon: numIcon(i + 1) }).bindTooltip(l.nama).addTo(layer));

        if (pts.length < 2) {
            distEl.textContent = pts.length ? '0 km (butuh minimal 2 lokasi untuk garis rute)' : '-';
            fit();
            return;
        }

        const mine = ++reqId;
        distEl.textContent = 'menghitung…';
        const coords = pts.map((l) => l.lng + ',' + l.lat).join(';');

        try {
            const res = await fetch('https://router.project-osrm.org/route/v1/driving/' + coords + '?overview=full&geometries=geojson');
            const data = await res.json();
            if (mine !== reqId) return;

            if (data.code === 'Ok' && data.routes && data.routes.length) {
                const line = data.routes[0].geometry.coordinates.map((c) => [c[1], c[0]]);
                L.polyline(line, { color: '#15803d', weight: 5, opacity: 0.85 }).addTo(layer);
                distEl.textContent = (data.routes[0].distance / 1000).toFixed(2) + ' km';
                fit();
                return;
            }
        } catch (e) { /* jatuh ke garis lurus */ }

        if (mine !== reqId) return;
        L.polyline(pts.map((l) => [l.lat, l.lng]), { color: '#15803d', weight: 4, dashArray: '8 8' }).addTo(layer);
        distEl.textContent = 'OSRM tidak tersedia, ditampilkan garis lurus';
        fit();
    }

    function refresh() {
        renderAvailable();
        renderSelected();
        clearTimeout(timer);
        timer = setTimeout(drawMap, 400);
    }

    Sortable.create(selEl, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: () => {
            selected = Array.from(selEl.children).map((li) => Number(li.dataset.id));
            refresh();
        }
    });

    searchEl.addEventListener('input', renderAvailable);
    kecEl.addEventListener('change', renderAvailable);

    refresh();
})();
</script>
