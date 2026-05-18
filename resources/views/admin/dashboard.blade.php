@extends('layouts.app')

@section('title', 'Dashboard Petugas - Sistem Antrian')

@push('styles')
<style>
    /* ─── Loket Panels ────────────────────────────────── */
    .loket-panel {
        border-radius: var(--radius-xl);
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .loket-panel:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    .loket-title {
        font-weight: 800;
        letter-spacing: 1px;
        font-size: 0.8rem;
    }
    .loket-nomor {
        font-size: 3rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -1px;
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
        padding: 1.25rem 1.5rem;
        position: relative;
    }
    .stat-number {
        font-size: 1.8rem;
        font-weight: 900;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        font-weight: 700;
    }
    .stat-icon {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.2rem;
        opacity: 0.15;
    }

    /* ─── Search / Filter ──────────────────────────────── */
    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 320px;
    }
    .search-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .search-wrapper .form-control {
        padding-left: 40px;
        height: 38px;
        font-size: 0.85rem;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-grid-fill me-2" style="color: var(--primary-light);"></i>
                Dashboard Antrian
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-calendar-event me-1"></i>
                Pelayanan BPS Kota Tegal • {{ Carbon\Carbon::today()->translatedFormat('d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('admin.daftar-manual') }}" class="btn btn-gradient py-2 px-4" style="border-radius: var(--radius-lg); font-weight: 600;">
                <i class="bi bi-person-plus-fill me-1"></i> Daftar Manual
            </a>
            @if(Auth::user()->role === 'admin')
                <form action="{{ route('admin.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset semua data antrian HARI INI? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="btn btn-danger-custom py-2 px-4" title="Reset semua antrian hari ini">
                        <i class="bi bi-trash-fill me-1"></i> Reset Harian
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- PANEL PANGGIL ANTRIAN (4 Panels in a responsive row) --}}
    <div class="row g-3 mb-4">
        {{-- Panel Konsultasi --}}
        <div class="col-sm-6 col-lg-3">
            <div class="loket-panel card-app text-center h-100 d-flex flex-column justify-content-between">
                <div class="loket-title py-2 text-white" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: var(--radius) var(--radius) 0 0;">
                    LOKET 1: KONSULTASI
                </div>
                <div class="p-4 flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="loket-nomor mb-3 text-primary fw-bold" id="panelSedangDipanggilKonsultasi">
                        {{ $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : '-' }}
                    </div>
                    <form action="{{ route('admin.panggil', 'Konsultasi') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-gradient w-100 py-2" style="border-radius: var(--radius-lg); font-weight: 600;">
                            <i class="bi bi-megaphone-fill me-1"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Pengaduan --}}
        <div class="col-sm-6 col-lg-3">
            <div class="loket-panel card-app text-center h-100 d-flex flex-column justify-content-between">
                <div class="loket-title py-2 text-white" style="background: linear-gradient(135deg, #EF4444, #B91C1C); border-radius: var(--radius) var(--radius) 0 0;">
                    LOKET 2: PENGADUAN
                </div>
                <div class="p-4 flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="loket-nomor mb-3 fw-bold" style="color: #EF4444;" id="panelSedangDipanggilPengaduan">
                        {{ $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : '-' }}
                    </div>
                    <form action="{{ route('admin.panggil', 'Pengaduan') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn w-100 py-2 text-white" style="background: linear-gradient(135deg, #EF4444, #B91C1C); border-radius: var(--radius-lg); font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">
                            <i class="bi bi-megaphone-fill me-1"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Rekomendasi Statistik --}}
        <div class="col-sm-6 col-lg-3">
            <div class="loket-panel card-app text-center h-100 d-flex flex-column justify-content-between">
                <div class="loket-title py-2 text-white" style="background: linear-gradient(135deg, #10B981, #047857); border-radius: var(--radius) var(--radius) 0 0;">
                    LOKET 3: STATISTIK
                </div>
                <div class="p-4 flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="loket-nomor mb-3 fw-bold" style="color: #10B981;" id="panelSedangDipanggilStatistik">
                        {{ $sedangDipanggilStatistik ? $sedangDipanggilStatistik->kode_antrian : '-' }}
                    </div>
                    <form action="{{ route('admin.panggil', 'Rekomendasi Statistik') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn w-100 py-2 text-white" style="background: linear-gradient(135deg, #10B981, #047857); border-radius: var(--radius-lg); font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                            <i class="bi bi-megaphone-fill me-1"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Perpustakaan --}}
        <div class="col-sm-6 col-lg-3">
            <div class="loket-panel card-app text-center h-100 d-flex flex-column justify-content-between">
                <div class="loket-title py-2 text-white" style="background: linear-gradient(135deg, #F59E0B, #B45309); border-radius: var(--radius) var(--radius) 0 0;">
                    LOKET 4: PERPUSTAKAAN
                </div>
                <div class="p-4 flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="loket-nomor mb-3 fw-bold" style="color: #F59E0B;" id="panelSedangDipanggilPerpustakaan">
                        {{ $sedangDipanggilPerpustakaan ? $sedangDipanggilPerpustakaan->kode_antrian : '-' }}
                    </div>
                    <form action="{{ route('admin.panggil', 'Perpustakaan') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn w-100 py-2 text-white" style="background: linear-gradient(135deg, #F59E0B, #B45309); border-radius: var(--radius-lg); font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                            <i class="bi bi-megaphone-fill me-1"></i> Panggil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN DATA HARI INI --}}
    <div class="row g-3 mb-4">
        {{-- Total Menunggu --}}
        <div class="col-4">
            <div class="card-app stat-card" style="border-top: 4px solid var(--primary); background: rgba(79, 70, 229, 0.03);">
                <div class="stat-number text-primary" id="totalMenunggu">{{ $totalMenunggu }}</div>
                <div class="stat-label">Menunggu</div>
                <i class="bi bi-hourglass-split stat-icon text-primary"></i>
            </div>
        </div>
        {{-- Total Selesai --}}
        <div class="col-4">
            <div class="card-app stat-card" style="border-top: 4px solid var(--success); background: rgba(16, 185, 129, 0.03);">
                <div class="stat-number text-success" id="totalSelesai">{{ $totalSelesai }}</div>
                <div class="stat-label">Selesai</div>
                <i class="bi bi-check-circle stat-icon text-success"></i>
            </div>
        </div>
        {{-- Total Dilewati --}}
        <div class="col-4">
            <div class="card-app stat-card" style="border-top: 4px solid #DB2777; background: rgba(219, 39, 119, 0.03);">
                <div class="stat-number text-danger" style="color: #DB2777 !important;" id="totalDilewati">{{ $totalDilewati }}</div>
                <div class="stat-label">Dilewati</div>
                <i class="bi bi-x-circle stat-icon" style="color: #DB2777;"></i>
            </div>
        </div>
    </div>

    {{-- DAFTAR ANTRIAN HARI INI --}}
    <div class="card-app p-4" style="min-height: 400px;">
        
        {{-- Toolbar / Filter --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h5 class="fw-bold mb-0 text-main flex-grow-1">
                <i class="bi bi-list-stars me-2 text-primary"></i>
                Daftar Antrian Hari Ini
            </h5>
            
            <div class="d-flex flex-wrap gap-2 align-items-center w-100 w-md-auto">
                {{-- Search --}}
                <div class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchKeyword" class="form-control" placeholder="Cari nama atau kode...">
                </div>
                
                {{-- Filter Status --}}
                <select id="filterStatus" class="form-select form-select-sm" style="border-radius: 20px; width: 150px; font-size: 0.8rem; border-color: var(--border-color); cursor: pointer;">
                    <option value="semua">Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="dipanggil">Sedang Dipanggil</option>
                    <option value="selesai">Selesai</option>
                    <option value="dilewati">Dilewati</option>
                </select>

                {{-- Filter Keperluan --}}
                <select id="filterKeperluan" class="form-select form-select-sm" style="border-radius: 20px; width: 150px; font-size: 0.8rem; border-color: var(--border-color); cursor: pointer;">
                    <option value="semua">Semua Layanan</option>
                    <option value="Konsultasi">Konsultasi</option>
                    <option value="Pengaduan">Pengaduan</option>
                    <option value="Rekomendasi Statistik">Rekomendasi Statistik</option>
                    <option value="Perpustakaan">Perpustakaan</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-app align-middle" id="tableAntrian">
                <thead>
                    <tr>
                        <th width="10%">KODE</th>
                        <th width="20%">PENGUNJUNG</th>
                        <th width="15%">KONTAK</th>
                        <th width="20%">LAYANAN</th>
                        <th width="15%">STATUS</th>
                        <th width="20%" class="text-end">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antrians as $item)
                        <tr class="antrian-row" 
                            data-nama="{{ strtolower($item->nama) }}" 
                            data-kode="{{ strtolower($item->kode_antrian) }}" 
                            data-status="{{ $item->status }}"
                            data-keperluan="{{ $item->keperluan }}">
                            <td>
                                <div class="badge-kode-antrian fs-6 py-2 px-3 fw-bold" 
                                     style="border-radius: 12px; display: inline-block;">
                                    {{ $item->kode_antrian }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: var(--text-main);">{{ $item->nama }}</div>
                                @if($item->nik)
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">NIK: {{ $item->nik }}</small>
                                @endif
                                <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">{{ Str::limit($item->alamat, 40) }}</small>
                            </td>
                            <td>
                                <div class="fw-medium" style="font-size: 0.85rem;">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $item->nomor_hp }}
                                </div>
                                @if($item->email)
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $item->email }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold" style="font-size: 0.85rem; color: var(--text-main);">{{ $item->keperluan }}</div>
                                @if($item->petugas)
                                    <small class="text-success d-block fw-semibold" style="font-size: 0.72rem;">
                                        <i class="bi bi-person-fill-check me-1"></i>Petugas: {{ $item->petugas->name }}
                                    </small>
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
                            </td>
                            <td class="text-end">
                                @if($item->status === 'menunggu')
                                    <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-danger-custom px-3 py-1" title="Lewati antrian">
                                            <i class="bi bi-arrow-right-short"></i> Lewati
                                        </button>
                                    </form>
                                @elseif($item->status === 'dipanggil')
                                    <button type="button" class="btn-success-custom px-3 py-1" onclick="showSelesaiModal({{ $item->id }})" title="Selesai dilayani">
                                        <i class="bi bi-check-lg"></i> Selesai
                                    </button>
                                    <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        <button type="submit" class="btn-danger-custom px-3 py-1" title="Lewati antrian">
                                            <i class="bi bi-arrow-right-short"></i> Lewati
                                        </button>
                                    </form>
                                @else
                                    <div class="text-end">
                                        <span class="text-muted fs-7 fw-semibold"><i class="bi bi-check2-all me-1 text-success"></i> Selesai</span>
                                        @if($item->catatan_petugas)
                                            <small class="text-muted d-block text-truncate" style="max-width: 150px; font-size: 0.65rem;" title="{{ $item->catatan_petugas }}">
                                                "{{ $item->catatan_petugas }}"
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center py-5 text-secondary">
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
                    <td colspan="6" class="text-center py-5 text-secondary">
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
                        <button type="submit" class="btn-danger-custom px-3 py-1" title="Lewati antrian"><i class="bi bi-arrow-right-short"></i> Lewati</button>
                    </form>
                `;
            } else if (item.status === 'dipanggil') {
                actions = `
                    <button type="button" class="btn-success-custom px-3 py-1" onclick="showSelesaiModal(${item.id})" title="Selesai dilayani"><i class="bi bi-check-lg"></i> Selesai</button>
                    <form action="${baseUrl}/admin/antrian/lewati/${item.id}" method="POST" class="d-inline ms-1">
                        <input type="hidden" name="_token" value="${csrf}">
                        <button type="submit" class="btn-danger-custom px-3 py-1" title="Lewati antrian"><i class="bi bi-arrow-right-short"></i> Lewati</button>
                    </form>
                `;
            } else {
                let noteHtml = item.catatan_petugas ? `<small class="text-muted d-block text-truncate" style="max-width: 150px; font-size: 0.65rem;" title="${item.catatan_petugas}">"${item.catatan_petugas}"</small>` : '';
                actions = `
                    <div class="text-end">
                        <span class="text-muted fs-7 fw-semibold"><i class="bi bi-check2-all me-1 text-success"></i> Selesai</span>
                        ${noteHtml}
                    </div>
                `;
            }

            let nikHtml = item.nik ? `<small class="text-muted d-block" style="font-size: 0.72rem;">NIK: ${item.nik}</small>` : '';
            let emailHtml = item.email ? `<small class="text-muted d-block" style="font-size: 0.72rem;">${item.email}</small>` : '';
            let petugasHtml = item.petugas ? `<small class="text-success d-block fw-semibold" style="font-size: 0.72rem;"><i class="bi bi-person-fill-check me-1"></i>Petugas: ${item.petugas.name}</small>` : '';

            html += `
                <tr class="antrian-row" 
                    data-nama="${item.nama.toLowerCase()}" 
                    data-kode="${item.kode_antrian.toLowerCase()}" 
                    data-status="${item.status}"
                    data-keperluan="${item.keperluan}">
                    <td>
                        <div class="badge-kode-antrian fs-6 py-2 px-3 fw-bold" style="border-radius: 12px; display: inline-block;">
                            ${item.kode_antrian}
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold" style="color: var(--text-main);">${item.nama}</div>
                        ${nikHtml}
                        <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">${item.alamat.substring(0, 40)}</small>
                    </td>
                    <td>
                        <div class="fw-medium" style="font-size: 0.85rem;"><i class="bi bi-whatsapp text-success me-1"></i>${item.nomor_hp}</div>
                        ${emailHtml}
                    </td>
                    <td>
                        <div class="fw-bold" style="font-size: 0.85rem; color: var(--text-main);">${item.keperluan}</div>
                        ${petugasHtml}
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-end">${actions}</td>
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
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyRow = document.getElementById('emptyRow');
        if (visibleRows === 0) {
            if (!emptyRow) {
                const tbody = document.querySelector('#tableAntrian tbody');
                const tr = document.createElement('tr');
                tr.id = 'emptyRow';
                tr.innerHTML = `
                    <td colspan="6" class="text-center py-5 text-secondary">
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
