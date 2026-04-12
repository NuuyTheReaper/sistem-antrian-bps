{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD ADMIN / RESEPSIONIS
     Manajemen antrian harian dengan aksi Panggil, Lewati, Selesai
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Antrian')

@push('styles')
<style>
    .qr-section {
        text-align: center;
        padding: 2rem;
    }
    .qr-container {
        display: inline-block;
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: 0 0 40px rgba(79, 70, 229, 0.15);
    }
    .qr-url {
        font-size: 0.8rem;
        color: var(--text-secondary);
        word-break: break-all;
        margin-top: 0.75rem;
        padding: 0.5rem 1rem;
        background: var(--dark-card);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-sm);
        display: inline-block;
        max-width: 350px;
    }
    .btn-print-qr {
        background: var(--dark-surface);
        border: 1px solid var(--glass-border);
        color: var(--text-primary);
        font-weight: 600;
        border-radius: var(--radius-sm);
        padding: 8px 20px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .btn-print-qr:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-speedometer2 me-2" style="color: var(--primary-light);"></i>
                Dashboard Antrian
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0 align-items-stretch">
            <a href="{{ route('admin.daftar-manual') }}" class="btn btn-gradient d-inline-flex align-items-center justify-content-center m-0">
                <i class="bi bi-person-plus-fill me-1"></i> Daftar Manual
            </a>
            <form action="{{ route('admin.reset') }}" method="POST" class="m-0 d-inline-flex"
                  onsubmit="return confirm('⚠️ PERHATIAN!\n\nAnda akan menghapus SEMUA data antrian hari ini.\nNomor antrian akan kembali ke 1.\n\nLanjutkan?');">
                @csrf
                <button type="submit" class="btn btn-danger-custom d-inline-flex align-items-center justify-content-center m-0" style="padding: 0.6rem 1.5rem;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Harian
                </button>
            </form>
        </div>
    </div>

    {{-- Statistik Cards --}}
    <div class="row g-3 mb-4">

        {{-- Nomor Sedang Dipanggil --}}
        <div class="col-6 col-lg-3">
            <div class="card-glass stat-card" style="border-top: 3px solid var(--primary-light);">
                <div class="stat-number" style="color: var(--primary-light);">
                    {{ $sedangDipanggil ? $sedangDipanggil->nomor_antrian : '-' }}
                </div>
                <div class="stat-label">Sedang Dipanggil</div>
            </div>
        </div>

        {{-- Total Menunggu --}}
        <div class="col-6 col-lg-3">
            <div class="card-glass stat-card" style="border-top: 3px solid var(--accent);">
                <div class="stat-number" style="color: var(--accent);">
                    {{ $totalMenunggu }}
                </div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>

        {{-- Total Selesai --}}
        <div class="col-6 col-lg-3">
            <div class="card-glass stat-card" style="border-top: 3px solid var(--success);">
                <div class="stat-number" style="color: var(--success);">
                    {{ $totalSelesai }}
                </div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        {{-- Total Dilewati --}}
        <div class="col-6 col-lg-3">
            <div class="card-glass stat-card" style="border-top: 3px solid var(--danger);">
                <div class="stat-number" style="color: var(--danger);">
                    {{ $totalDilewati }}
                </div>
                <div class="stat-label">Dilewati</div>
            </div>
        </div>
    </div>

    {{-- Tombol Panggil Berikutnya --}}
    <div class="text-center mb-4">
        <form action="{{ route('admin.panggil') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-gradient px-5 py-3" style="font-size: 1.1rem;">
                <i class="bi bi-megaphone-fill me-2"></i>
                Panggil Antrian Berikutnya
            </button>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         QR CODE — Scan untuk Pendaftaran Antrian
         ═══════════════════════════════════════════════════════════ --}}
    <div class="card-glass mb-4" style="overflow: hidden;">
        <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--glass-border);">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-qr-code me-2" style="color: var(--secondary);"></i>
                QR Code Pendaftaran
            </h5>
            <button class="btn-print-qr" onclick="printQR()">
                <i class="bi bi-printer me-1"></i> Cetak QR
            </button>
        </div>
        <div class="qr-section d-flex flex-column align-items-center text-center" id="qrPrintArea">
            <h3 class="fw-bold mb-1" style="color: var(--text-primary);">Scan QR Code</h3>
            <p class="text-secondary mb-3" style="font-size: 0.9rem;">Arahkan kamera HP Anda untuk mendaftar antrian</p>
            <div class="qr-container mx-auto" id="qrCodeContainer"></div>
            <div class="qr-url mt-3 mx-auto">
                <i class="bi bi-link-45deg me-1"></i>
                <span id="qrUrlText"></span>
            </div>
        </div>
    </div>

    {{-- Tabel Daftar Antrian --}}
    <div class="card-glass" style="overflow: hidden;">
        <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--glass-border);">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ol me-2" style="color: var(--secondary);"></i>
                Daftar Antrian Hari Ini
            </h5>
            <span class="badge rounded-pill px-3 py-2" style="background: var(--dark-surface); color: var(--text-secondary);">
                {{ $antrians->count() }} pengunjung
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-custom mb-0">
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
                <tbody>
                    @forelse($antrians as $item)
                        <tr class="{{ $item->status === 'dipanggil' ? 'table-active' : '' }}"
                            style="{{ $item->status === 'dipanggil' ? 'background: rgba(79,70,229,0.08);' : '' }}">

                            <td class="text-center fw-bold" style="font-size: 1.1rem;">
                                {{ $item->nomor_antrian }}
                            </td>

                            <td>
                                <div class="fw-semibold">{{ $item->nama }}</div>
                                <small class="text-secondary" style="font-size: 0.75rem;">{{ Str::limit($item->alamat, 40) }}</small>
                            </td>

                            <td>
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background: {{ $item->keperluan === 'Konsultasi' ? 'rgba(6,182,212,0.15)' : 'rgba(245,158,11,0.15)' }};
                                             color: {{ $item->keperluan === 'Konsultasi' ? '#06b6d4' : '#f59e0b' }};
                                             border: 1px solid {{ $item->keperluan === 'Konsultasi' ? 'rgba(6,182,212,0.3)' : 'rgba(245,158,11,0.3)' }};
                                             font-size: 0.75rem;">
                                    {{ $item->keperluan }}
                                </span>
                            </td>

                            <td style="font-size: 0.9rem;">{{ $item->nomor_hp }}</td>

                            <td class="text-center">
                                <span class="badge-status badge-{{ $item->status }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($item->status === 'menunggu')
                                    {{-- Tombol untuk antrian menunggu: tidak ada aksi langsung --}}
                                    <span class="text-secondary" style="font-size: 0.8rem;">
                                        <i class="bi bi-clock"></i> Menunggu giliran
                                    </span>

                                @elseif($item->status === 'dipanggil')
                                    {{-- Tombol Lewati & Selesai --}}
                                    <div class="d-flex gap-1 justify-content-center">
                                        <form action="{{ route('admin.lewati', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning-custom btn-sm px-3"
                                                    title="Lewati / Tunda">
                                                <i class="bi bi-skip-forward-fill me-1"></i> Skip
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.selesai', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success-custom btn-sm px-3"
                                                    title="Selesai dilayani">
                                                <i class="bi bi-check-lg me-1"></i> Selesai
                                            </button>
                                        </form>
                                    </div>

                                @elseif($item->status === 'selesai')
                                    <span class="text-success" style="font-size: 0.8rem;">
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    </span>

                                @elseif($item->status === 'dilewati')
                                    <span class="text-danger" style="font-size: 0.8rem;">
                                        <i class="bi bi-skip-forward-circle"></i> Dilewati
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                                <span class="text-secondary">Belum ada antrian hari ini</span>
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
    // ═══════════════════════════════════════════════════════════
    //  GENERATE QR CODE
    // ═══════════════════════════════════════════════════════════
    (function() {
        const daftarUrl = '{{ route("antrian.daftar") }}';
        const container = document.getElementById('qrCodeContainer');
        const urlText   = document.getElementById('qrUrlText');

        if (container) {
            new QRCode(container, {
                text: daftarUrl,
                width: 200,
                height: 200,
                colorDark: '#1e293b',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        if (urlText) {
            urlText.textContent = daftarUrl;
        }
    })();

    // ═══════════════════════════════════════════════════════════
    //  CETAK QR CODE — Buka jendela baru hanya berisi QR
    // ═══════════════════════════════════════════════════════════
    function printQR() {
        const container = document.getElementById('qrCodeContainer');
        if (!container) return;

        // Ambil gambar QR (bisa dari <img> atau <canvas>)
        let imgSrc = '';
        const imgEl = container.querySelector('img');
        const canvasEl = container.querySelector('canvas');

        if (imgEl && imgEl.src) {
            imgSrc = imgEl.src;
        } else if (canvasEl) {
            imgSrc = canvasEl.toDataURL('image/png');
        }

        if (!imgSrc) {
            alert('QR Code belum siap. Silakan tunggu sebentar.');
            return;
        }

        // Buka jendela baru yang hanya berisi QR di tengah
        const printWindow = window.open('', '_blank', 'width=600,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak QR Code</title>
                <style>
                    @page { size: A4 portrait; margin: 0; }
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    html, body {
                        width: 100%;
                        height: 100%;
                        background: white;
                    }
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    img {
                        width: 300px;
                        height: 300px;
                        display: block;
                    }
                </style>
            </head>
            <body>
                <img src="${imgSrc}" alt="QR Code" />
            </body>
            </html>
        `);
        printWindow.document.close();

        // Tunggu gambar dimuat lalu cetak
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    }
</script>
@endpush
