@extends('layouts.admin')
@section('title', 'Riwayat Login')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Login Aplikasi</h1>
        <p class="text-slate-500 mt-1">
            @if($filterUser)
                Menampilkan riwayat <span class="font-medium text-slate-700">{{ $filterUser->name }}</span>.
                <a href="{{ route('admin.riwayat-login.index') }}" class="text-emerald-700 hover:underline">Tampilkan semua</a>
            @else
                Semua percobaan login dari aplikasi. Data dihapus otomatis setelah 3 bulan.
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.riwayat-login.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            @if($filterUser)<input type="hidden" name="user" value="{{ $filterUser->id }}">@endif
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email, perangkat, atau IP..."
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Status</label>
                <select name="status" class="px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="">Semua</option>
                    @foreach($statuses as $kode => [$label])
                        <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">🔍 Filter</button>
            <a href="{{ route('admin.riwayat-login.index', $filterUser ? ['user' => $filterUser->id] : []) }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Waktu</th>
                    <th class="px-5 py-3.5">Pengguna</th>
                    <th class="px-5 py-3.5">Perangkat</th>
                    <th class="px-5 py-3.5">IP</th>
                    <th class="px-5 py-3.5 text-center">Hasil</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                @php [$label, $warna] = $statuses[$log->status] ?? [$log->status, 'bg-slate-200 text-slate-600']; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                        {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-slate-800">{{ $log->user?->name ?? 'Tidak dikenal' }}</div>
                        <div class="text-xs text-slate-400">{{ $log->email }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $log->device_name ?: '-' }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $log->ip_address ?: '-' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $warna }}">{{ $label }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada riwayat login.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">{{ $logs->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection