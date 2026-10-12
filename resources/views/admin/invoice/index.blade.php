@extends('layouts.admin')

@section('title', 'Retribusi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Retribusi</h1>
        <p class="text-slate-500 mt-1">Daftar invoice retribusi kendaraan & pungutan object retribusi</p>
    </div>

    {{-- Tab Sumber --}}
    <div class="flex gap-1 bg-slate-100 p-1 rounded-lg w-fit">
        <a href="{{ route('admin.invoice.index', ['sumber' => 'armada']) }}"
           class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $sumber === 'armada' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500 hover:text-slate-700' }}">
            Retribusi Armada
        </a>
        <a href="{{ route('admin.invoice.index', ['sumber' => 'pungutan']) }}"
           class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $sumber === 'pungutan' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500 hover:text-slate-700' }}">
            Retribusi Pungutan
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Invoice</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Lunas</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['paid'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Belum Bayar</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['unpaid'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Dibatalkan</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $summary['cancelled'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-200 bg-emerald-50">
            <p class="text-emerald-700 text-sm font-medium">Total Pendapatan</p>
            <p class="text-xl font-bold text-emerald-700 mt-1">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.invoice.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="sumber" value="{{ $sumber }}">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm"
                    placeholder="{{ $sumber === 'pungutan' ? 'Nomor invoice, nama object retribusi...' : 'Nomor invoice, armada, nomor polisi...' }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Status</label>
                <select name="status" class="w-44 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="">Semua</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors text-sm">
                    🔍 Filter
                </button>
                <a href="{{ route('admin.invoice.index', ['sumber' => $sumber]) }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>
<!-- Tempatkan blok ini tepat SETELAH div penutup Filter Bar yang sudah ada,
     sebelum div "Table" (bagian {{-- Table --}}) di invoice/index.blade.php -->

    @if($sumber === 'pungutan')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-wrap justify-between items-center gap-3">
        <div>
            <p class="text-sm font-medium text-slate-800">Laporan Rekap Pungutan</p>
            <p class="text-xs text-slate-500 mt-0.5">Lihat status bayar tiap object retribusi per tanggal, atau unduh sebagai Excel/PDF</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pungutan.report') }}"
                class="px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50 flex items-center gap-2">
                📋 Lihat Laporan
            </a>
            <a href="{{ route('admin.pungutan.report.export.excel') }}"
                class="px-4 py-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-medium text-sm flex items-center gap-2">
                📊 Export Excel
            </a>
            <a href="{{ route('admin.pungutan.report.export.pdf') }}"
                class="px-4 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-medium text-sm flex items-center gap-2">
                📄 Export PDF
            </a>
        </div>
    </div>
    @endif
    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <div class="text-sm text-slate-600">
                Menampilkan <span class="font-semibold text-slate-900">{{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-900">{{ $invoices->total() }}</span> data
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nomor Invoice</th>
                        @if($sumber === 'pungutan')
                            <th class="px-5 py-3.5">Object Retribusi</th>
                            <th class="px-5 py-3.5">Petugas</th>
                        @else
                            <th class="px-5 py-3.5">Armada</th>
                            <th class="px-5 py-3.5">Nomor Polisi</th>
                        @endif
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Total</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="invoiceTableBody">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-50 transition-colors" data-id="{{ $invoice->id }}">
                        <td class="px-5 py-4 font-mono font-semibold text-slate-700 text-xs">
                            {{ $invoice->nomor_invoice }}
                        </td>
                        @if($sumber === 'pungutan')
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">{{ $invoice->objectRetribusi->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $invoice->objectRetribusi->alamat ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $invoice->kunjungan->petugas->name ?? '-' }}
                            </td>
                        @else
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">{{ $invoice->armada->nama ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 uppercase text-xs">
                                {{ $invoice->gatepassLog->nomor_polisi ?? '-' }}
                            </td>
                        @endif
                        <td class="px-5 py-4 text-slate-600">
                            {{ $invoice->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-right font-semibold text-slate-900">
                            Rp {{ number_format($invoice->total, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusStyles = [
                                    'unpaid' => 'bg-amber-100 text-amber-800',
                                    'paid' => 'bg-emerald-100 text-emerald-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'unpaid' => 'Belum Bayar',
                                    'paid' => 'Lunas',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            <span class="invoice-status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusStyles[$invoice->status] }}">
                                {{ $statusLabels[$invoice->status] }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($invoice->status !== 'paid')
                                    <button onclick="updateInvoiceStatus({{ $invoice->id }}, 'paid', '{{ $sumber }}')"
                                        class="px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors" title="Tandai Lunas">
                                        Lunas
                                    </button>
                                @else
                                    <button onclick="updateInvoiceStatus({{ $invoice->id }}, 'unpaid', '{{ $sumber }}')"
                                        class="px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg transition-colors" title="Batalkan Lunas">
                                        Batal Lunas
                                    </button>
                                @endif
                                <a href="{{ $sumber === 'pungutan' ? route('admin.invoice.pungutan.print', $invoice) : route('admin.invoice.print', $invoice) }}" target="_blank"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Cetak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <p class="text-slate-500">Belum ada data invoice.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $invoices->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

</div>

<script>
function updateInvoiceStatus(id, status, sumber) {
    if (!confirm(status === 'paid' ? 'Tandai invoice ini sebagai LUNAS?' : 'Batalkan status lunas invoice ini?')) return;

    const base = sumber === 'pungutan' ? '{{ url('admin/invoice-pungutan') }}' : '{{ url('admin/invoice') }}';

    fetch(`${base}/${id}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: status }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Gagal memperbarui status');
        }
    })
    .catch(() => alert('Terjadi kesalahan, coba lagi'));
}
</script>
@endsection