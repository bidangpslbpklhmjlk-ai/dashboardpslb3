@extends('layouts.admin')

@section('title', 'Detail Armada - ' . $armada->nama)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Detail Armada</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.armada.index') }}">Armada</a></li>
                    <li class="breadcrumb-item active">{{ $armada->nama }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.armada.edit', $armada) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.armada.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Foto & Status -->
        <div class="col-lg-4 mb-4">
            <!-- Card Foto -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block">
                        <img src="{{ $armada->foto_url }}" 
                             alt="{{ $armada->nama }}" 
                             class="img-fluid rounded shadow-sm"
                             style="max-height: 280px; width: 100%; object-fit: cover;"
                             onerror="this.src='{{ asset('images/default-vehicle.png') }}'">
                        
                        <span class="position-absolute top-0 end-0 badge bg-{{ $armada->aktif ? 'success' : 'danger' }} m-2 px-3 py-2">
                            <i class="fas fa-{{ $armada->aktif ? 'check' : 'times' }}"></i>
                            {{ $armada->aktif ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                    
                    <h4 class="mt-3 mb-1">{{ $armada->nama }}</h4>
                    <p class="text-muted mb-0">{{ $armada->jenis_kendaraan ?? 'Jenis tidak ditentukan' }}</p>
                </div>
            </div>

            <!-- Card Ringkasan -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Ringkasan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Gatepass</span>
                        <span class="fw-bold">{{ $armada->gatepassLogs()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Invoice</span>
                        <span class="fw-bold">{{ $armada->invoices()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Terdaftar Sejak</span>
                        <span class="fw-bold">{{ $armada->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Informasi -->
        <div class="col-lg-8">
            <!-- Informasi Umum -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Umum</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Nama Armada</label>
                            <p class="fw-semibold mb-0">{{ $armada->nama }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Tipe</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $armada->isDinas() ? 'info' : 'secondary' }} fs-6">
                                    <i class="fas fa-{{ $armada->isDinas() ? 'building' : 'briefcase' }} me-1"></i>
                                    {{ $armada->tipe_label }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Pembayaran</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $armada->isGratis() ? 'success' : 'primary' }} fs-6">
                                    <i class="fas fa-{{ $armada->isGratis() ? 'gift' : 'money-bill' }} me-1"></i>
                                    {{ $armada->pembayaran_label }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Nomor Polisi</label>
                            <p class="fw-semibold mb-0 text-uppercase">{{ $armada->nomor_polisi ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Jenis Kendaraan</label>
                            <p class="fw-semibold mb-0">{{ $armada->jenis_kendaraan ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Tahun Pengadaan</label>
                            <p class="fw-semibold mb-0">{{ $armada->tahun_pengadaan ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Kondisi</label>
                            <p class="mb-0">
                                @php
                                    $kondisiColors = [
                                        'baik' => 'success',
                                        'rusak_ringan' => 'warning',
                                        'rusak_berat' => 'danger',
                                        'tidak_beroperasi' => 'dark',
                                    ];
                                    $kondisiColor = $kondisiColors[$armada->kondisi] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $kondisiColor }} fs-6">
                                    {{ $armada->kondisi_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Kontak -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Kontak</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Telepon</label>
                            <p class="fw-semibold mb-0">
                                @if($armada->telepon)
                                    <a href="tel:{{ $armada->telepon }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>{{ $armada->telepon }}
                                    </a>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small text-uppercase">Alamat</label>
                            <p class="fw-semibold mb-0">{{ $armada->alamat ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Gatepass -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Gatepass</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if($armada->gatepassLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal & Waktu</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($armada->gatepassLogs as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $log->keterangan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada riwayat gatepass</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Invoice -->
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Riwayat Invoice</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if($armada->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($armada->invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->nomor ?? $loop->iteration }}</td>
                                        <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                        <td>Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-success">Lunas</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada riwayat invoice</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="mt-4 text-end text-muted small">
                <p class="mb-0">Dibuat: {{ $armada->created_at->format('d M Y H:i:s') }} | 
                   Diperbarui: {{ $armada->updated_at->format('d M Y H:i:s') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection