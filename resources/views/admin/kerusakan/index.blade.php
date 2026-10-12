@extends('layouts.admin')
@section('content')
@php use App\Support\Label; @endphp
<div class="p-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Armada Rusak & Pengalihan</h1>
            <p class="text-sm text-slate-500">Tandai armada rusak, lalu alihkan rute / lokasi ke armada lain.</p>
        </div>
        <a href="{{ route('admin.kerusakan.create') }}" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">+ Tandai Armada Rusak</a>
    </div>

    @include('admin.partials.konflik')

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3 text-left">Armada</th><th class="px-4 py-3 text-left">Mulai</th><th class="px-4 py-3 text-left">Perkiraan selesai</th>
                    <th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Dialihkan</th><th class="px-4 py-3 text-left">Keterangan</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($items as $k)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ Label::of($k->armada) }}</td>
                    <td class="px-4 py-3">{{ $k->tanggal_mulai->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ optional($k->tanggal_selesai)->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $k->status === 'rusak' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-500' }}">{{ ucfirst($k->status) }}</span></td>
                    <td class="px-4 py-3">{{ $k->jumlah_dialihkan }} penugasan</td>
                    <td class="px-4 py-3 text-slate-500">{{ $k->keterangan }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('admin.kerusakan.show', $k) }}" class="text-green-700 font-medium hover:underline">Kelola</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Belum ada data kerusakan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection
