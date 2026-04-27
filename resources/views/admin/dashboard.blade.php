{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD ADMIN / RESEPSIONIS
     Manajemen antrian harian dengan aksi Panggil, Lewati, Selesai
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Antrian')

@push('styles')
<style>
    .qr-container {
        display: inline-block;
        background: white;
        padding: 1rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }
    .qr-url {
        font-size: 0.75rem;
        color: var(--text-muted);
        word-break: break-all;
        padding: 0.5rem 0.75rem;
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        display: inline-block;
        max-width: 100%;
    }
    .btn-print-qr {
        background: #F1F5F9;
        border: none;
        color: var(--text-main);
        font-weight: 600;
        border-radius: var(--radius-md);
        padding: 6px 12px;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 0.8rem;
    }
    .btn-print-qr:hover {
        background: var(--primary);
        color: white;
    }
    
    .stat-number {
        font-size: 3.5rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -1.5px;
    }

    .table-custom {
        margin-bottom: 0;
    }
    .table-custom th {
        background-color: #F8FAFC;
        border-bottom: 2px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 16px;
    }
    .table-custom td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }

    .btn-warning-custom {
        background: #FEF3C7;
        color: #D97706;
        border: none;
        border-radius: 8px;
        font-weight: 600;
    }
    .btn-success-custom {
        background: #D1FAE5;
        color: #059669;
        border: none;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-menunggu { background: #FEF3C7; color: #D97706; }
    .badge-dipanggil { background: #E0E7FF; color: #4338CA; }
    .badge-selesai { background: #D1FAE5; color: #059669; }
    .badge-dilewati { background: #FEE2E2; color: #DC2626; }

    .loket-card {
        border-top: 6px solid var(--primary);
        transition: transform 0.2s;
        position: relative;
        overflow: hidden;
    }
    .loket-card:hover {
        transform: translateY(-2px);
    }
    .loket-icon-bg {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.03;
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .rekap-card {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border-color) !important;
        box-shadow: none !important;
    }
    .rekap-icon-bg {
        position: absolute;
        right: -8px;
        bottom: -8px;
        font-size: 2.5rem;
        opacity: 0.05;
        transform: rotate(-15deg);
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.5rem; letter-spacing: -0.5px; color: var(--text-main);">
                Dashboard Antrian
            </h1>
            <p class="text-muted mb-0 fw-medium" style="font-size: 0.9rem;">
                <i class="bi bi-calendar3 me-1"></i>
                {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0 align-items-stretch">
            <a href="{{ route('admin.daftar-manual') }}" class="btn-app" style="padding: 10px 20px; font-size: 0.9rem; border-radius: 12px; box-shadow: none;">
                <i class="bi bi-person-plus-fill"></i> Daftar Manual
            </a>
            <form action="{{ route('admin.reset') }}" method="POST" class="m-0 d-inline-flex"
                  onsubmit="return confirm('⚠️ PERHATIAN!\n\nAnda akan menghapus SEMUA data antrian hari ini.\nNomor antrian akan kembali ke 1.\n\nLanjutkan?');">
                @csrf
                <button type="submit" class="btn btn-danger" style="border-radius: 12px; font-weight: 600; padding: 10px 20px; font-size: 0.9rem; background: #EF4444; border: none;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </form>
        </div>
    </div>

    {{-- Main Layout: Kiri (Pemanggilan & Rekap) | Kanan (QR Code) --}}
    <div class="row g-4 mb-4">
        
        {{-- KIRI: Pemanggilan & Rekap --}}
        <div class="col-12 col-lg-8 d-flex flex-column">
            
            {{-- Pemanggilan --}}
            <div class="row g-3 mb-4 flex-grow-1">

            {{-- Loket 1: Konsultasi --}}
                <div class="col-12 col-md-6">
                    <div class="card-app loket-card p-4 text-center h-100 d-flex flex-column" style="background: rgba(79, 70, 229, 0.03);">
                        <i class="bi bi-chat-dots loket-icon-bg" style="color: var(--primary);"></i>
                        <h6 class="fw-bold mb-3" style="color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bi bi-1-square-fill me-1 text-primary"></i> Konsultasi
                        </h6>
                        <div class="stat-number my-auto" id="statKonsultasi" style="color: var(--primary);">
                            {{ $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : '-' }}
                        </div>
                        <form action="{{ route('admin.panggil', 'Konsultasi') }}" method="POST" class="mt-auto">
                            @csrf
                            <button type="submit" class="btn-app w-100" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(79,70,229,0.2);">
                                <i class="bi bi-megaphone-fill"></i> Panggil Konsultasi
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Loket 2: Pengaduan --}}
                <div class="col-12 col-md-6">
                    <div class="card-app loket-card p-4 text-center h-100 d-flex flex-column" style="border-top-color: #cc2a3a; background: rgba(204, 42, 58, 0.03);">
                        <i class="bi bi-exclamation-octagon loket-icon-bg" style="color: #cc2a3a;"></i>
                        <h6 class="fw-bold mb-3" style="color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bi bi-2-square-fill me-1" style="color: #cc2a3a;"></i> Pengaduan
                        </h6>
                        <div class="stat-number my-auto" id="statPengaduan" style="color: #cc2a3a;">
                            {{ $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : '-' }}
                        </div>
                        <form action="{{ route('admin.panggil', 'Pengaduan') }}" method="POST" class="mt-auto">
                            @csrf
                            <button type="submit" class="btn-app w-100" style="background: #cc2a3a; border-color: #cc2a3a; box-shadow: 0 4px 15px rgba(204,42,58,0.3); border-radius: 12px;">
                                <i class="bi bi-megaphone-fill"></i> Panggil Pengaduan
                            </button>
                        </form>
                    </div>
                </div>
                
            </div>

            {{-- Rekap Harian (4 Kolom) --}}
            <div class="row g-3 mt-auto">
                <div class="col-6 col-md-3">
                    <div class="card-app p-3 text-center h-100 d-flex flex-column justify-content-center rekap-card" style="background: rgba(245, 158, 11, 0.03);">
                        <i class="bi bi-hourglass-split rekap-icon-bg" style="color: var(--warning);"></i>
                        <div class="fw-bold" id="totalMenunggu" style="color: var(--warning); font-size: 1.8rem; line-height: 1;">{{ $totalMenunggu }}</div>
                        <small class="text-muted fw-bold mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Menunggu</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-app p-3 text-center h-100 d-flex flex-column justify-content-center rekap-card" style="background: rgba(16, 185, 129, 0.03);">
                        <i class="bi bi-check-circle rekap-icon-bg" style="color: var(--secondary);"></i>
                        <div class="fw-bold" id="totalSelesai" style="color: var(--secondary); font-size: 1.8rem; line-height: 1;">{{ $totalSelesai }}</div>
                        <small class="text-muted fw-bold mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Selesai</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-app p-3 text-center h-100 d-flex flex-column justify-content-center rekap-card" style="background: rgba(239, 68, 68, 0.03);">
                        <i class="bi bi-x-circle rekap-icon-bg" style="color: var(--danger);"></i>
                        <div class="fw-bold" id="totalDilewati" style="color: var(--danger); font-size: 1.8rem; line-height: 1;">{{ $totalDilewati }}</div>
                        <small class="text-muted fw-bold mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Dilewati</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-app p-3 text-center h-100 d-flex flex-column justify-content-center rekap-card" style="background: rgba(79, 70, 229, 0.03);">
                        <i class="bi bi-people rekap-icon-bg" style="color: var(--primary);"></i>
                        <div class="fw-bold" id="totalPengunjung" style="color: var(--primary); font-size: 1.8rem; line-height: 1;">{{ $antrians->count() }}</div>
                        <small class="text-muted fw-bold mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total</small>
                    </div>
                </div>
            </div>

        </div>

        {{-- KANAN: QR Code --}}
        <div class="col-12 col-lg-4">
            <div class="card-app h-100 d-flex flex-column m-0">
                <div class="p-3 px-4 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color); background: #fdfdfd;">
                    <h6 class="mb-0 fw-bold" style="color: var(--text-main);">
                        <i class="bi bi-qr-code me-2 text-primary"></i>
                        QR Pendaftaran
                    </h6>
                    <button class="btn-print-qr" onclick="printQR()" title="Cetak QR Code">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
                <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                    <div class="qr-container mx-auto mb-3" id="qrCodeContainer"></div>
                    <div class="qr-url mt-2 mx-auto fw-medium">
                        <i class="bi bi-link-45deg"></i>
                        <span id="qrUrlText"></span>
                    </div>
                    <small class="text-muted mt-3" style="font-size: 0.75rem;">
                        Arahkan pengunjung untuk scan QR Code ini.
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabel Daftar Antrian --}}
    <div class="card-app">
        <div class="p-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="border-bottom: 1px solid var(--border-color); background: #fdfdfd;">
            <h6 class="mb-0 fw-bold" style="color: var(--text-main);">
                <i class="bi bi-list-ol me-2 text-primary"></i>
                Daftar Antrian Hari Ini
            </h6>
            
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Search --}}
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.8rem;"></i>
                    <input type="text" id="searchAntrian" class="form-control form-control-sm ps-5" placeholder="Cari Nama / No..." style="border-radius: 20px; width: 180px; font-size: 0.8rem; border-color: var(--border-color);">
                </div>

                {{-- Status Filter --}}
                <select id="filterStatus" class="form-select form-select-sm" style="border-radius: 20px; width: 150px; font-size: 0.8rem; border-color: var(--border-color); cursor: pointer;">
                    <option value="semua">Semua Status</option>
                    <option value="belum">Belum Terpanggil</option>
                    <option value="terpanggil">Terpanggil</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="dipanggil">Sedang Dipanggil</option>
                    <option value="selesai">Selesai</option>
                    <option value="dilewati">Dilewati</option>
                </select>

                {{-- Layanan Filter --}}
                <select id="filterLayanan" class="form-select form-select-sm" style="border-radius: 20px; width: 140px; font-size: 0.8rem; border-color: var(--border-color); cursor: pointer;">
                    <option value="semua">Semua Layanan</option>
                    <option value="Konsultasi">Konsultasi</option>
                    <option value="Pengaduan">Pengaduan</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">No.</th>
                        <th>Nama</th>
                        <th>Keperluan</th>
                        <th>No. HP</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableAntrianBody">
                    @forelse($antrians as $item)
                        <tr style="{{ $item->status === 'dipanggil' ? 'background: #EEF2FF;' : '' }}">

                            <td class="text-center fw-bold" style="font-size: 1.2rem; color: var(--primary-dark);">
                                {{ $item->kode_antrian }}
                            </td>

                            <td>
                                <div class="fw-bold" style="color: var(--text-main);">{{ $item->nama }}</div>
                                <small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">{{ Str::limit($item->alamat, 40) }}</small>
                            </td>

                            <td>
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background: {{ $item->keperluan === 'Konsultasi' ? '#EEF2FF' : '#FEE2E2' }};
                                             color: {{ $item->keperluan === 'Konsultasi' ? '#4338CA' : '#DC2626' }};
                                             font-size: 0.75rem; font-weight: 600;">
                                    {{ $item->keperluan }}
                                </span>
                            </td>

                            <td style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">{{ $item->nomor_hp }}</td>

                            <td class="text-center">
                                <span class="badge-status badge-{{ $item->status }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($item->status === 'menunggu')
                                    <span class="text-muted" style="font-size: 0.8rem; font-weight: 500;">
                                        <i class="bi bi-clock"></i> Menunggu
                                    </span>
                                @elseif($item->status === 'dipanggil')
                                    <div class="d-flex gap-2 justify-content-center">
                                        <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-warning-custom px-3 py-1" title="Lewati / Tunda">
                                                <i class="bi bi-skip-forward-fill"></i> Skip
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.selesai', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-success-custom px-3 py-1" title="Selesai dilayani">
                                                <i class="bi bi-check-lg"></i> Selesai
                                            </button>
                                        </form>
                                    </div>
                                @elseif($item->status === 'selesai')
                                    <span class="text-success fw-bold" style="font-size: 0.85rem;">
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    </span>
                                @elseif($item->status === 'dilewati')
                                    <span class="text-danger fw-bold" style="font-size: 0.85rem;">
                                        <i class="bi bi-skip-forward-circle-fill"></i> Dilewati
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted fw-medium">Belum ada antrian hari ini</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
{{-- QR Code Library (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    (function() {
        const daftarUrl = '{{ route("antrian.daftar") }}';
        const container = document.getElementById('qrCodeContainer');
        const urlText   = document.getElementById('qrUrlText');

        if (container) {
            new QRCode(container, {
                text: daftarUrl,
                width: 180,
                height: 180,
                colorDark: '#0F172A',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        if (urlText) urlText.textContent = daftarUrl;
    })();

    function printQR() {
        const container = document.getElementById('qrCodeContainer');
        if (!container) return;
        let imgSrc = '';
        const imgEl = container.querySelector('img');
        const canvasEl = container.querySelector('canvas');

        if (canvasEl) {
            imgSrc = canvasEl.toDataURL('image/png');
        } else if (imgEl && imgEl.src) {
            imgSrc = imgEl.src;
        }

        if (!imgSrc || imgSrc.startsWith('data:image/gif;base64,R0lGOD')) {
            alert('QR Code belum siap. Silakan tunggu sebentar.');
            return;
        }

        const printWindow = window.open('', '_blank', 'width=600,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak QR Code</title>
                <style>
                    @page { size: A4 portrait; margin: 0; }
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    html, body { width: 100%; height: 100%; background: white; font-family: sans-serif; }
                    body { display: flex; flex-direction: column; justify-content: center; align-items: center; }
                    h2 { margin-bottom: 20px; color: #0F172A; }
                    img { width: 300px; height: 300px; display: block; margin-bottom: 20px; }
                    p { color: #64748B; font-size: 14px; }
                </style>
            </head>
            <body>
                <h2>Scan QR Pendaftaran Antrian</h2>
                <img src="${imgSrc}" alt="QR Code" />
                <p>BPS Tegal</p>
            </body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
<script>
    let filterStatusValue = 'semua';
    let filterLayananValue = 'semua';
    let searchQuery = '';
    const baseUrl = '{{ url("/") }}';
    
    // State untuk melacak nomor terakhir yang dipanggil agar tidak double suara
    let lastSpokenKonsultasi = '{{ $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : "" }}';
    let lastSpokenPengaduan = '{{ $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : "" }}';

    // Inisialisasi data awal dari Blade agar filter langsung jalan tanpa nunggu polling
    let currentAntrianData = @json($antrians);

    document.getElementById('filterStatus').addEventListener('change', (e) => { filterStatusValue = e.target.value; updateTable(); });
    document.getElementById('filterLayanan').addEventListener('change', (e) => { filterLayananValue = e.target.value; updateTable(); });
    document.getElementById('searchAntrian').addEventListener('input', (e) => { searchQuery = e.target.value.toLowerCase(); updateTable(); });

    function updateTable() {
        let tbody = document.getElementById('tableAntrianBody');
        if (!tbody) return;

        let filtered = currentAntrianData.filter(item => {
            // Filter Layanan
            if (filterLayananValue !== 'semua' && item.keperluan !== filterLayananValue) return false;

            // Filter Status (Gunakan lowercase agar aman di hosting)
            const itemStatus = item.status.toLowerCase();
            if (filterStatusValue === 'belum' && itemStatus !== 'menunggu') return false;
            if (filterStatusValue === 'terpanggil' && itemStatus === 'menunggu') return false;
            if (filterStatusValue !== 'semua' && filterStatusValue !== 'belum' && filterStatusValue !== 'terpanggil' && itemStatus !== filterStatusValue.toLowerCase()) return false;

            // Search
            const nameMatch = item.nama ? item.nama.toLowerCase().includes(searchQuery) : false;
            const codeMatch = item.kode_antrian ? item.kode_antrian.toLowerCase().includes(searchQuery) : false;
            if (searchQuery && !nameMatch && !codeMatch) return false;

            return true;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5"><i class="bi bi-search fs-1 text-muted d-block mb-2"></i><span class="text-muted fw-medium">Data tidak ditemukan</span></td></tr>`;
            return;
        }

        let html = '';
        const csrfEl = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfEl ? csrfEl.content : '';

        filtered.forEach(item => {
            const rowStyle = item.status.toLowerCase() === 'dipanggil' ? 'background: #EEF2FF;' : '';
            const kpColor = item.keperluan === 'Konsultasi' ? '#EEF2FF' : '#FEE2E2';
            const kpTextC = item.keperluan === 'Konsultasi' ? '#4338CA' : '#DC2626';
            const statusU = item.status.charAt(0).toUpperCase() + item.status.slice(1);
            
            let aksi = '';
            const sLower = item.status.toLowerCase();
            if(sLower === 'menunggu') {
                aksi = `<span class="text-muted" style="font-size: 0.8rem; font-weight: 500;"><i class="bi bi-clock"></i> Menunggu</span>`;
            } else if(sLower === 'dipanggil') {
                aksi = `
                <div class="d-flex gap-2 justify-content-center">
                    <form action="${baseUrl}/admin/antrian/lewati/${item.id}" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="${csrf}">
                        <button type="submit" class="btn-warning-custom px-3 py-1" title="Lewati / Tunda"><i class="bi bi-skip-forward-fill"></i> Skip</button>
                    </form>
                    <form action="${baseUrl}/admin/antrian/selesai/${item.id}" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="${csrf}">
                        <button type="submit" class="btn-success-custom px-3 py-1" title="Selesai dilayani"><i class="bi bi-check-lg"></i> Selesai</button>
                    </form>
                </div>`;
            } else if(sLower === 'selesai') {
                aksi = `<span class="text-success fw-bold" style="font-size: 0.85rem;"><i class="bi bi-check-circle-fill"></i> Selesai</span>`;
            } else if(sLower === 'dilewati') {
                aksi = `<span class="text-danger fw-bold" style="font-size: 0.85rem;"><i class="bi bi-skip-forward-circle-fill"></i> Dilewati</span>`;
            }
            
            let addr = item.alamat || '';
            if(addr.length > 40) addr = addr.substring(0, 40) + '...';
            
            html += `
                <tr style="${rowStyle}">
                    <td class="text-center fw-bold" style="font-size: 1.2rem; color: var(--primary-dark);">${item.kode_antrian}</td>
                    <td><div class="fw-bold" style="color: var(--text-main);">${item.nama}</div><small class="text-muted" style="font-size: 0.75rem; font-weight: 500;">${addr}</small></td>
                    <td><span class="badge rounded-pill px-2 py-1" style="background: ${kpColor}; color: ${kpTextC}; font-size: 0.75rem; font-weight: 600;">${item.keperluan}</span></td>
                    <td style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">${item.nomor_hp}</td>
                    <td class="text-center"><span class="badge-status badge-${sLower}">${statusU}</span></td>
                    <td class="text-center">${aksi}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    // Panggil updateTable pertama kali
    updateTable();

    setInterval(function() {
        fetch('{{ route("api.admin.data") }}')
            .then(res => res.json())
            .then(data => {
                const spKonsultasi = document.getElementById('statKonsultasi');
                const spPengaduan = document.getElementById('statPengaduan');
                if(spKonsultasi) spKonsultasi.textContent = data.sedang_dipanggil_konsultasi;
                if(spPengaduan) spPengaduan.textContent = data.sedang_dipanggil_pengaduan;
                
                document.getElementById('totalPengunjung').textContent = data.antrians.length;
                document.getElementById('totalMenunggu').textContent = data.total_menunggu;
                document.getElementById('totalSelesai').textContent = data.total_selesai;
                document.getElementById('totalDilewati').textContent = data.total_dilewati;
                
                if (data.sedang_dipanggil_konsultasi !== '-' && data.sedang_dipanggil_konsultasi !== lastSpokenKonsultasi) {
                    lastSpokenKonsultasi = data.sedang_dipanggil_konsultasi;
                    if(typeof speakAntrian === 'function') speakAntrian(lastSpokenKonsultasi, 'Konsultasi');
                }
                if (data.sedang_dipanggil_pengaduan !== '-' && data.sedang_dipanggil_pengaduan !== lastSpokenPengaduan) {
                    lastSpokenPengaduan = data.sedang_dipanggil_pengaduan;
                    if(typeof speakAntrian === 'function') speakAntrian(lastSpokenPengaduan, 'Pengaduan');
                }

                currentAntrianData = data.antrians;
                updateTable();
            }).catch(e => console.log('Polling fail: ', e));
    }, 4000);

</script>
@endpush
