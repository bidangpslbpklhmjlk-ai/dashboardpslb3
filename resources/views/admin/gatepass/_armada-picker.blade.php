{{--
    Pemilih armada untuk form gatepass (create & edit).
    Variabel: $armadaOptions (array), $selectedId (nullable)
    Alur: ketuk kolom -> pilih TIPE armada -> pilih nomor polisi/nama.
    Mengetik di kolom langsung mencari di semua tipe.
    Yang dikirim ke server hanya armada_id.
--}}
@php
    $tipeLabel = collect(\App\Models\Armada::TIPE)->map(fn ($t) => $t['label'])->all();
@endphp

<script>
function armadaPicker(options, selectedId, tipeLabel) {
    return {
        options: options,
        tipeLabel: tipeLabel,
        query: '',
        open: false,
        selected: null,
        tipeAktif: null,

        init() {
            if (selectedId) {
                this.pick(this.options.find(o => o.id == selectedId));
            }
        },

        label(t) { return this.tipeLabel[t] || t || 'Lainnya'; },

        // true kalau pengguna sedang mengetik (bukan sekadar menampilkan nopol terpilih)
        get mencari() {
            const q = this.query.trim().toUpperCase();
            if (!q) return false;
            return !(this.selected && q === this.selected.nomor_polisi.toUpperCase());
        },

        // langkah 1: daftar tipe + jumlah, urut sesuai Armada::TIPE
        get tipeList() {
            const hitung = {};
            this.options.forEach(o => { hitung[o.tipe] = (hitung[o.tipe] || 0) + 1; });
            const urut = Object.keys(this.tipeLabel).filter(k => hitung[k])
                .concat(Object.keys(hitung).filter(k => !(k in this.tipeLabel)));
            return urut.map(k => ({ kode: k, nama: this.label(k), jumlah: hitung[k] }));
        },

        // langkah 2 (atau hasil pencarian)
        get filtered() {
            if (this.mencari) {
                const q = this.query.trim().toUpperCase();
                return this.options.filter(o =>
                    (o.nomor_polisi + ' ' + (o.nama || '')).toUpperCase().includes(q)
                );
            }
            return this.options.filter(o => o.tipe === this.tipeAktif);
        },

        get tampilTipe() { return !this.mencari && this.tipeAktif === null; },

        bukaMenu() {
            this.open = true;
            this.tipeAktif = null; // selalu mulai dari pilihan tipe
        },

        pickTipe(kode) { this.tipeAktif = kode; },

        pick(o) {
            if (!o) return;
            this.selected = o;
            this.query = o.nomor_polisi;
            this.open = false;
        },

        onInput() {
            this.selected = null;
            this.open = true;
        },
    };
}
</script>

<div class="space-y-5"
     x-data="armadaPicker({{ \Illuminate\Support\Js::from($armadaOptions) }}, {{ \Illuminate\Support\Js::from($selectedId) }}, {{ \Illuminate\Support\Js::from($tipeLabel) }})">

    {{-- Nomor Polisi --}}
    <div class="relative" @click.away="open = false">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Polisi <span class="text-red-500">*</span></label>

        <div class="relative">
            <input type="text" x-model="query" required autocomplete="off"
                   x-effect="$el.setCustomValidity(selected ? '' : 'Pilih armada dari daftar yang tersedia.')"
                   @focus="bukaMenu(); $el.select()"
                   @click="if (!open) bukaMenu()"
                   @input="onInput()"
                   @keydown.enter.prevent="if (!tampilTipe && filtered.length) pick(filtered[0])"
                   @keydown.escape="open = false"
                   placeholder="Pilih tipe, atau ketik nomor polisi / nama..."
                   class="w-full px-3 py-2.5 pr-10 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm uppercase font-semibold"
                   :class="selected ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : ''">
            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg x-show="!selected" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <svg x-show="selected" x-cloak class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>

        <input type="hidden" name="armada_id" :value="selected ? selected.id : ''">

        <div x-show="open" x-cloak
             class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-auto">

            {{-- Langkah 1: pilih tipe --}}
            <template x-if="tampilTipe">
                <div>
                    <p class="px-4 pt-2.5 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Pilih tipe armada</p>
                    <template x-for="t in tipeList" :key="t.kode">
                        <button type="button" @click="pickTipe(t.kode)"
                                class="w-full px-4 py-2.5 text-left hover:bg-emerald-50 border-b border-slate-50 last:border-0 flex justify-between items-center gap-3">
                            <span class="text-sm font-medium text-slate-800" x-text="t.nama"></span>
                            <span class="text-xs text-slate-500">
                                <span x-text="t.jumlah"></span> armada ›
                            </span>
                        </button>
                    </template>
                    <div x-show="tipeList.length === 0" class="p-4 text-center">
                        <p class="text-sm text-slate-500">Belum ada armada aktif.</p>
                    </div>
                </div>
            </template>

            {{-- Langkah 2: pilih armada dari tipe terpilih / hasil pencarian --}}
            <template x-if="!tampilTipe">
                <div>
                    <button type="button" x-show="!mencari" @click="tipeAktif = null"
                            class="w-full px-4 py-2.5 text-left text-sm font-semibold text-emerald-700 hover:bg-emerald-50 border-b border-slate-100">
                        ‹ <span x-text="label(tipeAktif)"></span>
                    </button>

                    <template x-for="o in filtered" :key="o.id">
                        <button type="button" @click="pick(o)"
                                class="w-full px-4 py-2.5 text-left hover:bg-emerald-50 border-b border-slate-50 last:border-0 flex justify-between items-center gap-3">
                            <div class="min-w-0">
                                <span class="font-bold text-slate-800 uppercase" x-text="o.nomor_polisi"></span>
                                <span class="text-xs text-slate-500 ml-2" x-text="o.jenis_kendaraan || 'Jenis belum diisi'"></span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-medium text-slate-600" x-text="o.nama"></span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full ml-1"
                                      :class="o.tipe === 'dinas_lh' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                                      x-text="label(o.tipe)"></span>
                            </div>
                        </button>
                    </template>

                    <div x-show="filtered.length === 0" class="p-4 text-center">
                        <p class="text-sm text-slate-500">Armada tidak ditemukan.</p>
                        <p class="text-xs text-slate-400 mt-1">Daftarkan dulu lewat menu Data Armada.</p>
                    </div>
                </div>
            </template>
        </div>

        @error('armada_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Tipe Kendaraan (dari armada, tidak bisa diubah) --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe Kendaraan</label>
        <div class="relative px-3 py-2.5 border rounded-lg text-sm font-medium"
             :class="selected ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-dashed border-slate-300 bg-slate-50 text-slate-400'">
            <span x-text="selected ? (selected.jenis_kendaraan || 'Belum diisi') : 'Pilih nomor polisi terlebih dahulu'"></span>
            <span x-show="selected" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Auto</span>
        </div>
        <p x-show="selected && !selected.jenis_kendaraan" x-cloak class="text-xs text-amber-600 mt-1">
            Jenis kendaraan armada ini belum diisi, akan dicatat sebagai "Lainnya". Lengkapi di menu Armada.
        </p>
    </div>

    {{-- Perusahaan / Dinas (dari armada) --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Perusahaan / Dinas</label>

        <div x-show="selected" x-cloak class="px-3 py-2.5 border border-emerald-200 bg-emerald-50 rounded-lg">
            <div class="flex justify-between items-center gap-3">
                <p class="text-sm font-medium text-emerald-800" x-text="selected?.nama"></p>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                      :class="selected?.tipe === 'dinas_lh' ? 'bg-blue-200 text-blue-700' : 'bg-emerald-200 text-emerald-700'"
                      x-text="selected ? label(selected.tipe) : ''"></span>
            </div>
            <p class="text-[10px] text-emerald-600 mt-1" x-text="selected?.gratis ? '✅ Gratis' : '💰 Berbayar'"></p>
        </div>

        <div x-show="!selected" class="px-3 py-2.5 border border-dashed border-slate-300 bg-slate-50 rounded-lg text-sm text-slate-400">
            Pilih nomor polisi terlebih dahulu
        </div>
    </div>
</div>