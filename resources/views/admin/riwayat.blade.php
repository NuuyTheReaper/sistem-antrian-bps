@extends('layouts.app')

@section('title', 'Riwayat Antrian - Sistem Antrian')

@push('styles')
<style>
    /* ─── Badge Status ────────────────────────────────── */
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

    .card-filter {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-clock-history me-2" style="color: var(--primary-light);"></i>
                Riwayat Antrian
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-person-fill-check me-1"></i>
                Daftar antrian terlayani lengkap dengan petugas pemroses & catatan pelayanan.
            </p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="card-app card-filter p-4 mb-4">
        <form action="{{ route('admin.riwayat') }}" method="GET" class="row g-3 align-items-end">
            {{-- Tanggal --}}
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="color: var(--text-main);"><i class="bi bi-calendar3 me-1"></i>Pilih Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control" style="height: auto; padding: 10px 16px; border-radius: 12px;" required>
            </div>
            
            {{-- Keperluan --}}
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="color: var(--text-main);"><i class="bi bi-tag-fill me-1"></i>Pilih Layanan</label>
                <select name="keperluan" class="form-select" style="height: auto; padding: 10px 16px; border-radius: 12px;" required>
                    <option value="semua" {{ $keperluan === 'semua' ? 'selected' : '' }}>Semua Layanan</option>
                    <option value="Konsultasi" {{ $keperluan === 'Konsultasi' ? 'selected' : '' }}>Konsultasi</option>
                    <option value="Pengaduan" {{ $keperluan === 'Pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                    <option value="Rekomendasi Statistik" {{ $keperluan === 'Rekomendasi Statistik' ? 'selected' : '' }}>Rekomendasi Statistik</option>
                    <option value="Perpustakaan" {{ $keperluan === 'Perpustakaan' ? 'selected' : '' }}>Perpustakaan</option>
                </select>
            </div>

            {{-- Button --}}
            <div class="col-12 col-md-4">
                <button type="submit" class="btn btn-gradient w-100" style="border-radius: 12px; font-weight: 600; padding: 12px 20px; font-size: 0.95rem;">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Riwayat Table --}}
    <div class="card-app p-4">
        <div class="table-responsive">
            <table class="table table-app align-middle">
                <thead>
                    <tr>
                        <th width="10%">KODE</th>
                        <th width="25%">DATA PENGUNJUNG</th>
                        <th width="15%">KONTAK & LAYANAN</th>
                        <th width="15%">STATUS</th>
                        <th width="15%">PETUGAS</th>
                        <th width="20%">CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $item)
                        <tr>
                            <td>
                                <div class="badge-kode-antrian fs-6 py-2 px-3 fw-bold" style="border-radius: 12px; display: inline-block;">
                                    {{ $item->kode_antrian }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: var(--text-main); font-size: 0.95rem;">{{ $item->nama }}</div>
                                @if($item->nik)
                                    <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-card-id me-1 text-primary"></i>NIK: {{ $item->nik }}</small>
                                @endif
                                @if($item->jenis_kelamin)
                                    <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-gender-ambiguous me-1 text-primary"></i>{{ $item->jenis_kelamin }}</small>
                                @endif
                                <small class="text-secondary d-block mt-1" style="font-size: 0.75rem; font-weight: 500;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $item->alamat }}
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary" style="font-size: 0.85rem;">{{ $item->keperluan }}</div>
                                <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: 500;">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $item->nomor_hp }}
                                </div>
                                @if($item->email)
                                    <small class="text-muted d-block" style="font-size: 0.72rem;"><i class="bi bi-envelope me-1"></i>{{ $item->email }}</small>
                                @endif
                                @if($item->pekerjaan)
                                    <small class="text-muted d-block" style="font-size: 0.72rem;"><i class="bi bi-briefcase me-1"></i>{{ $item->pekerjaan }} ({{ $item->pendidikan_terakhir }})</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $item->status }}">
                                    @if($item->status === 'menunggu')
                                        <i class="bi bi-hourglass-split"></i> Menunggu
                                    @elseif($item->status === 'dipanggil')
                                        <i class="bi bi-volume-up-fill"></i> Dipanggil
                                    @elseif($item->status === 'selesai')
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    @elseif($item->status === 'dilewati')
                                        <i class="bi bi-exclamation-circle-fill"></i> Dilewati
                                    @endif
                                </span>
                                @if($item->waktu_dipanggil)
                                    <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">
                                        Panggil: {{ $item->waktu_dipanggil->format('H:i:s') }}
                                    </small>
                                @endif
                                @if($item->waktu_selesai)
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">
                                        Selesai: {{ $item->waktu_selesai->format('H:i:s') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($item->petugas)
                                    <div class="fw-bold text-success" style="font-size: 0.85rem;">
                                        <i class="bi bi-person-circle me-1"></i>{{ $item->petugas->name }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ ucfirst($item->petugas->role) }}</small>
                                @else
                                    <span class="text-secondary" style="font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->catatan_petugas)
                                    <div class="p-2 bg-light border" style="border-radius: 8px; font-size: 0.78rem; font-style: italic; color: #475569;">
                                        "{{ $item->catatan_petugas }}"
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">Tidak ada catatan.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <i class="bi bi-clock fs-2 mb-2 d-block opacity-50"></i>
                                <span class="fw-semibold">Tidak ditemukan data antrian untuk tanggal dan layanan ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
