@extends('layouts.app')

@section('title', 'Dashboard Petugas - Sistem Antrian')

@push('styles')
<style>
    /* ─── Loket Panels ────────────────────────────────── */
    .loket-panel {
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        position: relative;
        z-index: 1;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .loket-panel:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }
    .loket-accent {
        position: absolute;
        top: 0; left: 0; right: 0; height: 6px;
        z-index: 2;
    }
    .loket-title-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 1px;
        margin-bottom: 0.75rem;
    }
    .loket-nomor {
        font-size: 4rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -2px;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    .loket-bg-icon {
        position: absolute;
        right: -15px;
        top: 15px;
        font-size: 7rem;
        opacity: 0.03;
        transform: rotate(-15deg);
        z-index: 0;
        pointer-events: none;
    }
    .btn-loket {
        border-radius: 16px;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        padding: 14px 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-loket:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

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

    /* ─── Stat Cards ──────────────────────────────────── */
    .stat-card {
        background: #ffffff;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        border-radius: 20px;
        display: flex;
        align-items: center;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
    }
    .stat-icon-wrap {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    /* ─── Search / Filter ──────────────────────────────── */
    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 280px;
    }
    .search-wrapper i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    .search-wrapper .form-control {
        padding-left: 50px;
        padding-right: 20px;
        height: 46px;
        font-size: 0.95rem;
        border-radius: 23px;
        background: rgba(255, 255, 255, 0.85);
        border: 2px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        backdrop-filter: blur(10px);
        color: var(--text-main);
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .search-wrapper .form-control:focus {
        background: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15), 0 8px 25px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    .search-wrapper .form-control:focus + i {
        transform: translateY(-50%) scale(1.15);
    }

    .filter-select-custom {
        height: 46px;
        padding: 10px 38px 10px 22px;
        font-size: 0.92rem;
        border-radius: 23px;
        background-color: rgba(255, 255, 255, 0.9);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%234f46e5' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 12px 12px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
        backdrop-filter: blur(12px);
        color: #334155;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .filter-select-custom:focus, .filter-select-custom:hover {
        background-color: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 6px 25px rgba(79, 70, 229, 0.12), 0 2px 10px rgba(79, 70, 229, 0.08);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* Smooth Row Transitions */
    .antrian-row {
        transition: opacity 0.3s ease, transform 0.3s ease;
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    .antrian-row.hiding {
        opacity: 0 !important;
        transform: scale(0.95) translateY(-10px) !important;
        pointer-events: none;
    }


    /* ─── Responsive Mobile Viewport Adjustments ────────── */

    @media (max-width: 576px) {
        .loket-nomor {
            font-size: 3.2rem !important;
        }
        .loket-panel .p-4 {
            padding: 1.5rem !important;
        }
        .btn-loket {
            padding: 12px 16px !important;
        }
        .stat-card {
            padding: 1.25rem !important;
        }
        .stat-icon-wrap {
            width: 50px; height: 50px; font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1" style="color: var(--text-main); letter-spacing: -0.5px;">
                <i class="bi bi-grid-fill me-2" style="color: var(--primary);"></i>
                Dashboard Antrian
            </h1>
            <p class="text-secondary mb-0 fw-medium" style="font-size: 0.95rem;">
                <i class="bi bi-calendar-event me-1 text-primary"></i>
                Pelayanan BPS Kota Tegal • {{ Carbon\Carbon::today()->translatedFormat('d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('admin.daftar-manual') }}" class="btn btn-gradient text-white d-inline-flex align-items-center justify-content-center" style="border-radius: 20px; font-weight: 600; padding: 10px 24px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2); transition: all 0.3s;">
                <i class="bi bi-person-plus-fill me-2"></i> Daftar Manual
            </a>
            @if(Auth::user()->role === 'admin')
                <form action="{{ route('admin.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset semua data antrian HARI INI? Tindakan ini tidak dapat dibatalkan.')" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-gradient-danger text-white d-inline-flex align-items-center justify-content-center" style="border-radius: 20px; font-weight: 600; padding: 10px 24px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2); transition: all 0.3s;" title="Reset semua antrian hari ini">
                        <i class="bi bi-trash-fill me-2"></i> Reset Harian
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- PANEL PANGGIL ANTRIAN (4 Panels in a responsive row) --}}
    <div class="row g-3 mb-5">
        {{-- Panel Konsultasi --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="loket-panel h-100 d-flex flex-column">
                <div class="loket-accent" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));"></div>
                <i class="bi bi-chat-dots-fill loket-bg-icon" style="color: var(--primary);"></i>
                <div class="p-4 flex-grow-1 d-flex flex-column align-items-center text-center position-relative" style="z-index: 2;">
                    <div class="loket-title-badge" style="background: rgba(79, 70, 229, 0.1); color: var(--primary);">
                        <i class="bi bi-headset me-1"></i> LOKET 1
                    </div>
                    <div class="text-secondary fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Konsultasi</div>
                    <div class="loket-nomor" style="color: var(--primary-dark);" id="panelSedangDipanggilKonsultasi">
                        {{ $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : '-' }}
                    </div>
                </div>
                <div class="px-4 pb-4 position-relative" style="z-index: 2;">
                    <form action="{{ route('admin.panggil', 'Konsultasi') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-loket w-100 text-white" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);">
                            <i class="bi bi-volume-up-fill me-2"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Pengaduan --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="loket-panel h-100 d-flex flex-column">
                <div class="loket-accent" style="background: linear-gradient(135deg, #EF4444, #B91C1C);"></div>
                <i class="bi bi-megaphone-fill loket-bg-icon" style="color: #EF4444;"></i>
                <div class="p-4 flex-grow-1 d-flex flex-column align-items-center text-center position-relative" style="z-index: 2;">
                    <div class="loket-title-badge" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> LOKET 2
                    </div>
                    <div class="text-secondary fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Pengaduan</div>
                    <div class="loket-nomor" style="color: #B91C1C;" id="panelSedangDipanggilPengaduan">
                        {{ $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : '-' }}
                    </div>
                </div>
                <div class="px-4 pb-4 position-relative" style="z-index: 2;">
                    <form action="{{ route('admin.panggil', 'Pengaduan') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-loket w-100 text-white" style="background: linear-gradient(135deg, #EF4444, #B91C1C); box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);">
                            <i class="bi bi-volume-up-fill me-2"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Rekomendasi Statistik --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="loket-panel h-100 d-flex flex-column">
                <div class="loket-accent" style="background: linear-gradient(135deg, #10B981, #047857);"></div>
                <i class="bi bi-bar-chart-fill loket-bg-icon" style="color: #10B981;"></i>
                <div class="p-4 flex-grow-1 d-flex flex-column align-items-center text-center position-relative" style="z-index: 2;">
                    <div class="loket-title-badge" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                        <i class="bi bi-pie-chart-fill me-1"></i> LOKET 3
                    </div>
                    <div class="text-secondary fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Statistik</div>
                    <div class="loket-nomor" style="color: #047857;" id="panelSedangDipanggilStatistik">
                        {{ $sedangDipanggilStatistik ? $sedangDipanggilStatistik->kode_antrian : '-' }}
                    </div>
                </div>
                <div class="px-4 pb-4 position-relative" style="z-index: 2;">
                    <form action="{{ route('admin.panggil', 'Rekomendasi Statistik') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-loket w-100 text-white" style="background: linear-gradient(135deg, #10B981, #047857); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);">
                            <i class="bi bi-volume-up-fill me-2"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Perpustakaan --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="loket-panel h-100 d-flex flex-column">
                <div class="loket-accent" style="background: linear-gradient(135deg, #F59E0B, #B45309);"></div>
                <i class="bi bi-book-fill loket-bg-icon" style="color: #F59E0B;"></i>
                <div class="p-4 flex-grow-1 d-flex flex-column align-items-center text-center position-relative" style="z-index: 2;">
                    <div class="loket-title-badge" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                        <i class="bi bi-journal-bookmark-fill me-1"></i> LOKET 4
                    </div>
                    <div class="text-secondary fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Perpustakaan</div>
                    <div class="loket-nomor" style="color: #B45309;" id="panelSedangDipanggilPerpustakaan">
                        {{ $sedangDipanggilPerpustakaan ? $sedangDipanggilPerpustakaan->kode_antrian : '-' }}
                    </div>
                </div>
                <div class="px-4 pb-4 position-relative" style="z-index: 2;">
                    <form action="{{ route('admin.panggil', 'Perpustakaan') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-loket w-100 text-white" style="background: linear-gradient(135deg, #F59E0B, #B45309); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);">
                            <i class="bi bi-volume-up-fill me-2"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN DATA HARI INI --}}
    <div class="row g-3 mb-5">
        {{-- Total Menunggu --}}
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap me-3" style="background: rgba(79, 70, 229, 0.1); color: var(--primary);">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-label text-muted mb-1" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Menunggu</div>
                    <div class="stat-number fw-bold" style="font-size: 1.75rem; color: var(--text-main); line-height: 1;" id="totalMenunggu">{{ $totalMenunggu }}</div>
                </div>
            </div>
        </div>
        {{-- Total Selesai --}}
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap me-3" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-label text-muted mb-1" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Selesai Dilayani</div>
                    <div class="stat-number fw-bold" style="font-size: 1.75rem; color: var(--text-main); line-height: 1;" id="totalSelesai">{{ $totalSelesai }}</div>
                </div>
            </div>
        </div>
        {{-- Total Dilewati --}}
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrap me-3" style="background: rgba(219, 39, 119, 0.1); color: #DB2777;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-label text-muted mb-1" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Dilewati</div>
                    <div class="stat-number fw-bold" style="font-size: 1.75rem; color: var(--text-main); line-height: 1;" id="totalDilewati">{{ $totalDilewati }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR ANTRIAN HARI INI --}}
    <div style="min-height: 400px;">
        
        {{-- Toolbar / Filter --}}
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
            <h5 class="fw-bold mb-0 text-main d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-list-stars"></i>
                </div>
                Daftar Antrian Hari Ini
            </h5>
            
            <div class="d-flex flex-wrap gap-3 align-items-center">
                {{-- Search --}}
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchKeyword" class="form-control form-control-solid" placeholder="Cari nama / kode...">
                </div>
                
                {{-- Filter Status --}}
                <select id="filterStatus" class="form-select filter-select-custom" style="width: 180px;">
                    <option value="semua">Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="dipanggil">Dipanggil</option>
                    <option value="selesai">Selesai</option>
                    <option value="dilewati">Dilewati</option>
                </select>

                {{-- Filter Keperluan --}}
                <select id="filterKeperluan" class="form-select filter-select-custom" style="width: 220px;">
                    <option value="semua">Semua Layanan</option>
                    <option value="Konsultasi">Konsultasi</option>
                    <option value="Pengaduan">Pengaduan</option>
                    <option value="Rekomendasi Statistik">Rekomendasi Statistik</option>
                    <option value="Perpustakaan">Perpustakaan</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive" style="overflow-x: visible;">
            <table class="table table-floating table-mobile-cards align-middle" id="tableAntrian">
                <thead>
                    <tr>
                        <th width="10%">KODE</th>
                        <th width="20%">PENGUNJUNG</th>
                        <th width="15%">KONTAK</th>
                        <th width="18%">LAYANAN</th>
                        <th width="12%">STATUS</th>
                        <th width="15%">CATATAN</th>
                        <th width="10%" class="text-end">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antrians as $item)
                        <tr class="antrian-row" 
                            data-nama="{{ strtolower($item->nama) }}" 
                            data-kode="{{ strtolower($item->kode_antrian) }}" 
                            data-status="{{ $item->status }}"
                            data-keperluan="{{ $item->keperluan }}">
                            <td data-label="Kode">
                                <div class="badge-kode-antrian fs-6 py-2 px-3 fw-bold text-nowrap" 
                                     style="border-radius: 12px; display: inline-block;">
                                    {{ $item->kode_antrian }}
                                </div>
                            </td>
                            <td data-label="Pengunjung">
                                <div class="fw-bold" style="color: var(--text-main);">{{ $item->nama }}</div>
                                @if($item->nik)
                                    <small class="text-muted d-block text-nowrap" style="font-size: 0.72rem;">NIK: {{ $item->nik }}</small>
                                @endif
                                <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">{{ Str::limit($item->alamat, 40) }}</small>
                            </td>
                            <td data-label="Kontak">
                                <div class="fw-medium text-nowrap" style="font-size: 0.85rem;">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $item->nomor_hp }}
                                </div>
                                @if($item->email)
                                    <small class="text-muted d-block text-nowrap" style="font-size: 0.72rem;">{{ $item->email }}</small>
                                @endif
                            </td>
                            <td data-label="Layanan">
                                <div class="fw-bold" style="font-size: 0.85rem; color: var(--text-main);">{{ $item->keperluan }}</div>
                                @if($item->petugas)
                                    <small class="text-success d-block fw-semibold" style="font-size: 0.72rem;">
                                        <i class="bi bi-person-fill-check me-1"></i>Petugas: {{ $item->petugas->name }}
                                    </small>
                                @endif
                            </td>
                            <td data-label="Status">
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
                            </td>
                            <td data-label="Catatan">
                                @if($item->status === 'selesai' && $item->catatan_petugas)
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-flex align-items-center px-3 py-1.5 text-truncate mx-2" 
                                              style="border-radius: 8px; font-size: 0.72rem; max-width: 150px; font-weight: 500; background-color: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.15); color: var(--text-muted);" 
                                              title="{{ $item->catatan_petugas }}">
                                            <span class="text-truncate">{{ $item->catatan_petugas }}</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted opacity-50 mx-2">-</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-end">
                                @if($item->status === 'menunggu')
                                    <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold" style="background: transparent; color: #EF4444; border: 1px solid #FECDD3; border-radius: 20px; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'; this.style.borderColor='#FCA5A5';" onmouseout="this.style.background='transparent'; this.style.borderColor='#FECDD3';" title="Lewati antrian">
                                            Lewati <i class="bi bi-arrow-right-short ms-1"></i>
                                        </button>
                                    </form>
                                @elseif($item->status === 'dipanggil')
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold text-white" style="background: #10B981; border: none; border-radius: 20px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);" onclick="showSelesaiModal({{ $item->id }})" title="Selesai dilayani">
                                            <i class="bi bi-check-lg me-1"></i> Selesai
                                        </button>
                                        <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold" style="background: transparent; color: #EF4444; border: 1px solid #FECDD3; border-radius: 20px; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'; this.style.borderColor='#FCA5A5';" onmouseout="this.style.background='transparent'; this.style.borderColor='#FECDD3';" title="Lewati antrian">
                                                Lewati <i class="bi bi-arrow-right-short ms-1"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-end">
                                        <span class="badge bg-light text-secondary border fw-bold px-3 py-2" style="border-radius: 12px;"><i class="bi bi-check2-all me-1 text-success"></i> Tuntas</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-2 mb-2 d-block opacity-50"></i>
                                <span class="fw-semibold">Belum ada data antrian hari ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

{{-- Modal Selesai Pelayanan --}}
<div class="modal fade" id="selesaiModal" tabindex="-1" aria-labelledby="selesaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px;">
                <h5 class="modal-title fw-bold" id="selesaiModalLabel">
                    <i class="bi bi-check-circle-fill text-success me-2"></i> Selesaikan Pelayanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSelesaiModal" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <div class="mb-3">
                        <label for="catatan_petugas" class="form-label fw-semibold" style="color: var(--text-main);">Catatan Pelayanan (Opsional)</label>
                        <textarea class="form-control" name="catatan_petugas" id="catatan_petugas" rows="3" placeholder="Masukkan catatan pelayanan, saran, atau hasil konsultasi pengunjung..." style="border-radius: 12px; font-size: 0.9rem; border-color: var(--border-color);"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem;">Batal</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #10B981; border: none; padding: 8px 20px;">Simpan & Selesai</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ═══════════════════════════════════════════════════════════
    //  GLOBAL VARIABLES & TOKEN
    // ═══════════════════════════════════════════════════════════
    const baseUrl = "{{ url('/') }}";
    const csrf    = "{{ csrf_token() }}";

    // ═══════════════════════════════════════════════════════════
    //  FUNGSI TRIGGER MODAL SELESAI
    // ═══════════════════════════════════════════════════════════
    function showSelesaiModal(id) {
        const form = document.getElementById('formSelesaiModal');
        form.action = `${baseUrl}/admin/antrian/selesai/${id}`;
        document.getElementById('catatan_petugas').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('selesaiModal'));
        modal.show();
    }

    // ═══════════════════════════════════════════════════════════
    //  AJAX POLLING (Melakukan refresh data setiap 4 detik)
    // ═══════════════════════════════════════════════════════════
    let isSearchActive = false;

    // Detect search & filter focus
    document.getElementById('searchKeyword').addEventListener('input', () => { isSearchActive = true; runSearch(); });
    document.getElementById('filterStatus').addEventListener('change', () => { isSearchActive = true; runSearch(); });
    document.getElementById('filterKeperluan').addEventListener('change', () => { isSearchActive = true; runSearch(); });

    setInterval(async () => {
        // Jangan timpa render jika user sedang mengetik pencarian/filter aktif
        const searchInput = document.getElementById('searchKeyword').value.trim();
        const filterVal = document.getElementById('filterStatus').value;
        const filterKepVal = document.getElementById('filterKeperluan').value;
        
        if (searchInput !== "" || filterVal !== "semua" || filterKepVal !== "semua") {
            isSearchActive = true;
        } else {
            isSearchActive = false;
        }

        if (isSearchActive) return;

        try {
            const res = await fetch(`{{ route('api.admin.data') }}`);
            if (!res.ok) throw new Error('Network failed');
            const data = await res.json();

            // 1. Update stats counter
            document.getElementById('totalMenunggu').textContent = data.total_menunggu;
            document.getElementById('totalSelesai').textContent  = data.total_selesai;
            document.getElementById('totalDilewati').textContent  = data.total_dilewati;

            // 2. Update panel nomor antrian sedang dipanggil
            document.getElementById('panelSedangDipanggilKonsultasi').textContent = data.sedang_dipanggil_konsultasi;
            document.getElementById('panelSedangDipanggilPengaduan').textContent = data.sedang_dipanggil_pengaduan;
            document.getElementById('panelSedangDipanggilStatistik').textContent = data.sedang_dipanggil_statistik;
            document.getElementById('panelSedangDipanggilPerpustakaan').textContent = data.sedang_dipanggil_perpustakaan;

            // 3. Update daftar tabel
            updateTable(data.antrians);
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 4000);

    // ═══════════════════════════════════════════════════════════
    //  UPDATE TABLE HTML SECARA DINAMIS
    // ═══════════════════════════════════════════════════════════
    function updateTable(antrians) {
        const tbody = document.querySelector('#tableAntrian tbody');
        
        if (antrians.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyRow">
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-inbox fs-2 mb-2 d-block opacity-50"></i>
                        <span class="fw-semibold">Belum ada data antrian hari ini.</span>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        antrians.forEach(item => {
            let statusBadge = '';
            if (item.status === 'menunggu') {
                statusBadge = '<span class="badge-status badge-menunggu"><i class="bi bi-hourglass-split"></i> Menunggu</span>';
            } else if (item.status === 'dipanggil') {
                statusBadge = '<span class="badge-status badge-dipanggil"><i class="bi bi-volume-up-fill"></i> Dipanggil</span>';
            } else if (item.status === 'selesai') {
                statusBadge = '<span class="badge-status badge-selesai"><i class="bi bi-check-circle-fill"></i> Selesai</span>';
            } else if (item.status === 'dilewati') {
                statusBadge = '<span class="badge-status badge-dilewati"><i class="bi bi-exclamation-circle-fill"></i> Dilewati</span>';
            }

            let actions = '';
            if (item.status === 'menunggu') {
                actions = `
                    <form action="${baseUrl}/admin/antrian/lewati/${item.id}" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="${csrf}">
                        <button type="submit" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold" style="background: transparent; color: #EF4444; border: 1px solid #FECDD3; border-radius: 20px; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'; this.style.borderColor='#FCA5A5';" onmouseout="this.style.background='transparent'; this.style.borderColor='#FECDD3';" title="Lewati antrian">
                            Lewati <i class="bi bi-arrow-right-short ms-1"></i>
                        </button>
                    </form>
                `;
            } else if (item.status === 'dipanggil') {
                actions = `
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <button type="button" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold text-white" style="background: #10B981; border: none; border-radius: 20px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);" onclick="showSelesaiModal(${item.id})" title="Selesai dilayani">
                            <i class="bi bi-check-lg me-1"></i> Selesai
                        </button>
                        <form action="${baseUrl}/admin/antrian/lewati/${item.id}" method="POST" class="m-0">
                            <input type="hidden" name="_token" value="${csrf}">
                            <button type="submit" class="btn btn-sm d-inline-flex align-items-center px-3 py-1 fw-bold" style="background: transparent; color: #EF4444; border: 1px solid #FECDD3; border-radius: 20px; transition: all 0.2s;" onmouseover="this.style.background='#FEF2F2'; this.style.borderColor='#FCA5A5';" onmouseout="this.style.background='transparent'; this.style.borderColor='#FECDD3';" title="Lewati antrian">
                                Lewati <i class="bi bi-arrow-right-short ms-1"></i>
                            </button>
                        </form>
                    </div>
                `;
            } else {
                actions = `
                    <div class="text-end">
                        <span class="badge bg-light text-secondary border fw-bold px-3 py-2" style="border-radius: 12px;"><i class="bi bi-check2-all me-1 text-success"></i> Tuntas</span>
                    </div>
                `;
            }

            let noteHtml = (item.status === 'selesai' && item.catatan_petugas) ? `
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center px-3 py-1.5 text-truncate mx-2" 
                          style="border-radius: 8px; font-size: 0.72rem; max-width: 150px; font-weight: 500; background-color: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.15); color: var(--text-muted);" 
                          title="${item.catatan_petugas}">
                        <span class="text-truncate">${item.catatan_petugas}</span>
                    </span>
                </div>
            ` : '<span class="text-muted opacity-50 mx-2">-</span>';

            let nikHtml = item.nik ? `<small class="text-muted d-block text-nowrap" style="font-size: 0.72rem;">NIK: ${item.nik}</small>` : '';
            let emailHtml = item.email ? `<small class="text-muted d-block text-nowrap" style="font-size: 0.72rem;">${item.email}</small>` : '';
            let petugasHtml = item.petugas ? `<small class="text-success d-block fw-semibold" style="font-size: 0.72rem;"><i class="bi bi-person-fill-check me-1"></i>Petugas: ${item.petugas.name}</small>` : '';

            html += `
                <tr class="antrian-row" 
                    data-nama="${item.nama.toLowerCase()}" 
                    data-kode="${item.kode_antrian.toLowerCase()}" 
                    data-status="${item.status}"
                    data-keperluan="${item.keperluan}">
                    <td data-label="Kode">
                        <div class="badge-kode-antrian fs-6 py-2 px-3 fw-bold text-nowrap" style="border-radius: 12px; display: inline-block;">
                            ${item.kode_antrian}
                        </div>
                    </td>
                    <td data-label="Pengunjung">
                        <div class="fw-bold" style="color: var(--text-main);">${item.nama}</div>
                        ${nikHtml}
                        <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">${item.alamat.substring(0, 40)}</small>
                    </td>
                    <td data-label="Kontak">
                        <div class="fw-medium text-nowrap" style="font-size: 0.85rem;"><i class="bi bi-whatsapp text-success me-1"></i>${item.nomor_hp}</div>
                        ${emailHtml}
                    </td>
                    <td data-label="Layanan">
                        <div class="fw-bold" style="font-size: 0.85rem; color: var(--text-main);">${item.keperluan}</div>
                        ${petugasHtml}
                    </td>
                    <td data-label="Status">${statusBadge}</td>
                    <td data-label="Catatan">${noteHtml}</td>
                    <td data-label="Aksi" class="text-end">${actions}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        runSearch(); // Apply filters immediately after dynamic update
    }

    // ═══════════════════════════════════════════════════════════
    //  PENCARIAN DAN FILTER CLIENT-SIDE
    // ═══════════════════════════════════════════════════════════
    function runSearch() {
        const query     = document.getElementById('searchKeyword').value.toLowerCase().trim();
        const statusVal = document.getElementById('filterStatus').value;
        const kepVal    = document.getElementById('filterKeperluan').value;
        const rows      = document.querySelectorAll('.antrian-row');
        let visibleRows = 0;

        rows.forEach(row => {
            const nama  = row.getAttribute('data-nama');
            const kode  = row.getAttribute('data-kode');
            const stat  = row.getAttribute('data-status');
            const kep   = row.getAttribute('data-keperluan');

            // Pencarian nama/kode
            const matchSearch = nama.includes(query) || kode.includes(query);
            // Filter status
            const matchStatus = (statusVal === 'semua') || (stat === statusVal);
            // Filter keperluan
            const matchKep    = (kepVal === 'semua') || (kep === kepVal);

            if (matchSearch && matchStatus && matchKep) {
                if (row.style.display === 'none') {
                    row.style.display = '';
                    row.classList.add('hiding');
                    void row.offsetHeight; // force reflow
                    row.classList.remove('hiding');
                }
                visibleRows++;
            } else {
                if (row.style.display !== 'none' && !row.classList.contains('hiding')) {
                    row.classList.add('hiding');
                    setTimeout(() => {
                        if (row.classList.contains('hiding')) {
                            row.style.display = 'none';
                            row.classList.remove('hiding');
                        }
                    }, 250);
                }
            }
        });

        const emptyRow = document.getElementById('emptyRow');
        if (visibleRows === 0) {
            if (!emptyRow) {
                const tbody = document.querySelector('#tableAntrian tbody');
                const tr = document.createElement('tr');
                tr.id = 'emptyRow';
                tr.innerHTML = `
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-search fs-2 mb-2 d-block opacity-50"></i>
                        <span class="fw-semibold">Tidak ditemukan kecocokan data antrian.</span>
                    </td>
                `;
                tbody.appendChild(tr);
            } else {
                emptyRow.style.display = '';
                emptyRow.querySelector('span').textContent = 'Tidak ditemukan kecocokan data antrian.';
            }
        } else {
            if (emptyRow) emptyRow.style.display = 'none';
        }
    }
</script>
@endpush
