@extends('layouts.admin')

@section('content')
<div class="p-8 max-w-7xl mx-auto">
    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Daftar Berita</h1>
            <p class="text-slate-500 text-sm">Manajemen konten berita yang tampil di website utama.</p>
        </div>
        {{-- Tombol Tambah Berita --}}
        <a href="{{ route('admin.berita.create') }}" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition">
            + Tambah Berita
        </a>
    </div>

    {{-- Wadah Tabel Berita --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
        <table id="beritaTable" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <th class="p-4 w-16 text-center">No</th>
                    <th class="p-4">Judul Berita</th>
                    <th class="p-4 w-40 text-center" data-sortable="false">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($items as $index => $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">{{ $index + 1 }}</td>
                        <td class="p-4 font-semibold text-slate-800">{{ $item->title }}</td>
                        <td class="p-4">
                            <div class="flex justify-center gap-2">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('admin.berita.edit', $item->id) }}" class="px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold hover:bg-amber-100 transition">
                                    Edit
                                </a>
                                {{-- Tombol Delete --}}
                                <form action="{{ route('admin.berita.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-slate-400 bg-slate-50/30">
                            Belum ada berita yang diterbitkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tambahan Asset Simple-DataTables --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>

{{-- Kustomisasi Style agar Selaras dengan Tailwind --}}
<style>
    .datatable-wrapper .datatable-top {
        padding: 0 0 1.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .datatable-wrapper .datatable-bottom {
        padding: 1.5rem 0 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        margin-top: 1rem;
    }
    .datatable-selector {
        border-radius: 0.75rem;
        border-color: #e2e8f0;
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #334155;
    }
    .datatable-selector:focus {
        border-color: #3b82f6;
        outline: none;
    }
    .datatable-input {
        border-radius: 0.75rem;
        border-color: #e2e8f0;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        width: 250px;
    }
    .datatable-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .datatable-pagination a {
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #475569;
        font-weight: 600;
        transition: all 0.2s;
    }
    .datatable-pagination .datatable-active a {
        background-color: #3b82f6 !important;
        color: white !important;
    }
    .datatable-pagination a:hover {
        background-color: #f1f5f9;
    }
    .datatable-info {
        font-size: 0.875rem;
        color: #64748b;
    }
</style>

{{-- Inisialisasi JavaScript DataTable --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dataTable = new simpleDatatables.DataTable("#beritaTable", {
            searchable: true,
            perPage: 10,
            perPageSelect: [5, 10, 20, 50],
            labels: {
                placeholder: "Cari berita...",
                perPage: "data per halaman",
                noRows: "Tidak ada berita yang ditemukan",
                info: "Menampilkan {start} sampai {end} dari {rows} berita",
            }
        });
    });
</script>
@endsection