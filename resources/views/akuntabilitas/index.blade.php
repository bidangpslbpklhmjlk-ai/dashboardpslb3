@extends('layouts.admin')

@section('title', 'Dokumen Akuntabilitas')

@section('content')

<div class="container-fluid">

    <div class="row mb-3">

        <div class="col-md-6">
            <h4>
                <i class="fas fa-file-pdf text-danger"></i>
                Dokumen Akuntabilitas
            </h4>
        </div>

        <div class="col-md-6 text-end">

            <a href="{{ route('admin.akuntabilitas.create') }}"
               class="btn btn-primary">

                <i class="fas fa-plus"></i>
                Tambah Dokumen

            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif

    <div class="card">

        <div class="card-header">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">

                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="Cari judul dokumen..."
                               value="{{ request('keyword') }}">

                    </div>

                    <div class="col-md-2">

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="fas fa-search"></i>
                            Cari

                        </button>

                    </div>

                </div>

            </form>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover align-middle">

                    <thead class="table-light">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Dokumen
                        </th>

                        <th width="180">
                            Jenis
                        </th>

                        <th width="100">
                            Tahun
                        </th>

                        <th width="120">
                            Status
                        </th>

                        <th width="140">
                            Upload
                        </th>

                        <th width="140">
                            Uploader
                        </th>

                        <th width="100">
                            Download
                        </th>

                        <th width="280">
                            Aksi
                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($items as $item)

                        <tr>

                            <td>

                                {{ $items->firstItem() + $loop->index }}

                            </td>

                            <td>

                                <strong>

                                    {{ $item->judul }}

                                </strong>

                                <br>

                                <small class="text-muted">

                                    {{ $item->nama_file }}

                                </small>

                            </td>

                            <td>

                                <span class="badge bg-info">

                                    {{ $item->jenis }}

                                </span>

                            </td>

                            <td>

                                {{ $item->tahun }}

                            </td>

                            <td>

                                @if($item->is_publish)

                                    <span class="badge bg-success">

                                        Publish

                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        Draft

                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ optional($item->created_at)->format('d-m-Y') }}

                            </td>

                            <td>

                                {{ $item->creator->name ?? '-' }}

                            </td>

                            <td>

                                <span class="badge bg-primary">

                                    {{ number_format($item->jumlah_download ?? 0) }}

                                </span>

                            </td>

                            <td>

                                <a href="{{ asset('storage/'.$item->path_file) }}"
                                   target="_blank"
                                   class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a href="{{ route('admin.akuntabilitas.download', $item->id) }}"
                                   class="btn btn-success btn-sm">

                                    <i class="fas fa-download"></i>

                                </a>

                                <a href="{{ route('admin.akuntabilitas.edit', $item->id) }}"
                                   class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <form action="{{ route('admin.akuntabilitas.destroy', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger btn-sm">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center">

                                <div class="py-4">

                                    <i class="fas fa-folder-open fa-2x text-muted"></i>

                                    <p class="mt-2">

                                        Belum ada dokumen akuntabilitas.

                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        @if($items->hasPages())

            <div class="card-footer">

                {{ $items->links() }}

            </div>

        @endif

    </div>

</div>

@endsection