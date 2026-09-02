@extends('layouts.app')

@section('title', 'Preview Laporan - Sistem Antrian')

@push('styles')
<style>
    .card-filter {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .badge-status {
        padding: 6px 14px;
        border-radius: var(--radius-xl);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-menunggu { background: #EEF2FF; color: var(--primary); }
    .badge-dipanggil { background: #ECFDF5; color: var(--success); }
    .badge-selesai { background: #F1F5F9; color: var(--text-muted); }
    .badge-dilewati { background: #FDF2F8; color: #DB2777; }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-file-earmark-text me-2" style="color: var(--primary-light);"></i>
                Preview Laporan
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-funnel me-1"></i>
                Data Laporan Tahun: <strong>{{ $tahun }}</strong>, Bulan: <strong>{{ $bulan === 'semua' ? 'Semua Bulan' : date('F', mktime(0, 0, 0, $bulan, 10)) }}</strong>, Layanan: <strong>{{ $keperluan === 'semua' ? 'Semua Layanan' : $keperluan }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.laporan') }}" class="btn btn-light" style="border-radius: 12px; font-weight: 600;">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            
            <a href="{{ route('admin.laporan.download', ['tahun' => $tahun, 'bulan' => $bulan, 'keperluan' => $keperluan]) }}" class="btn btn-success" style="border-radius: 12px; font-weight: 600; background: #10B981; border: none;">
                <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Unduh CSV
            </a>

            <button type="button" class="btn btn-primary" style="border-radius: 12px; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#modalCetak">
                <i class="bi bi-printer-fill me-1"></i> Cetak PDF
            </button>
        </div>
    </div>

    @if(session('shared_link'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);">
        <i class="bi bi-link-45deg fs-4 me-3"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Tautan Publik Laporan Berhasil Dibuat!</h6>
            <p class="mb-0 text-dark">Tautan ini berlaku selama 7 hari dan dapat diakses oleh Kepala BPS tanpa perlu login:</p>
            <div class="input-group mt-2">
                <input type="text" class="form-control bg-white" id="sharedLinkInput" value="{{ session('shared_link') }}" readonly>
                <button class="btn btn-success" type="button" onclick="navigator.clipboard.writeText(document.getElementById('sharedLinkInput').value); alert('Tautan berhasil disalin ke clipboard!');">Salin Tautan</button>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Data Table --}}
    <div class="card-app p-0" style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead style="position: sticky; top: 0; background: #fdfdfd; z-index: 1;">
                    <tr>
                        <th class="ps-4">No. Antrian</th>
                        <th>Tanggal</th>
                        <th>Data Pengunjung</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Waktu Layanan</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="badge bg-light text-dark border fs-6 py-2 px-3 fw-bold">
                                    {{ $item->kode_antrian }}
                                </div>
                            </td>
                            <td>{{ $item->tanggal_antrian->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->nama }}</div>
                                @if($item->nik)
                                    <small class="text-muted d-block"><i class="bi bi-card-id me-1"></i>{{ $item->nik }}</small>
                                @endif
                                <small class="text-secondary d-block mt-1">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $item->nomor_hp }}
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">{{ $item->keperluan }}</div>
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $item->status }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                @if($item->waktu_dipanggil && $item->waktu_selesai)
                                    <small class="text-muted d-block">
                                        Mulai: {{ $item->waktu_dipanggil->format('H:i') }}
                                    </small>
                                    <small class="text-muted d-block">
                                        Selesai: {{ $item->waktu_selesai->format('H:i') }}
                                    </small>
                                    <div class="fw-semibold text-dark mt-1" style="font-size: 0.8rem;">
                                        Durasi: {{ ceil($item->waktu_dipanggil->diffInSeconds($item->waktu_selesai) / 60) }} Menit
                                    </div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->catatan_petugas ?? '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-2 mb-2 d-block opacity-50"></i>
                                <span class="fw-semibold">Tidak ada data untuk periode dan layanan ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form Cetak --}}
    <div class="modal fade" id="modalCetak" tabindex="-1" aria-labelledby="modalCetakLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.laporan.cetak') }}" method="GET" target="_blank" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCetakLabel">Pengaturan Tanda Tangan Cetak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="keperluan" value="{{ $keperluan }}">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kepala BPS</label>
                        <input type="text" name="nama_kepala" class="form-control" placeholder="Masukkan nama lengkap beserta gelar" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">NIP</label>
                        <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP (contoh: 19800101 200501 1 001)" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" formaction="{{ route('admin.laporan.generate-link') }}" formmethod="POST" formtarget="_self" class="btn btn-outline-success">
                        <i class="bi bi-link-45deg me-1"></i> Buat Link Publik
                    </button>
                    <button type="submit" class="btn btn-primary" onclick="setTimeout(() => { document.querySelector('#modalCetak .btn-close').click(); }, 100);">
                        Lanjutkan Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
