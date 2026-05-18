{{-- ═══════════════════════════════════════════════════════════
     LAPORAN PEMANTAUAN PENGUNJUNG
     Grafik mingguan/bulanan pengunjung antrian
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Laporan Pemantauan - Sistem Antrian')

@push('styles')
<style>
    /* ─── Periode Tabs ─────────────────────────────────── */
    .periode-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .periode-btn {
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 8px 20px;
        border-radius: var(--radius-xl);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .periode-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: #EEF2FF;
    }
    .periode-btn.active {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-color: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    /* ─── Ringkasan Cards ──────────────────────────────── */
    .ringkasan-card {
        text-align: center;
        padding: 1.5rem 1rem;
    }
    .ringkasan-number {
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 4px;
    }
    .ringkasan-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        font-weight: 700;
        margin-top: 5px;
    }

    .ringkasan-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 4rem;
        opacity: 0.05;
        transform: rotate(-15deg);
    }

    /* ─── Chart Container ──────────────────────────────── */
    .chart-wrapper {
        position: relative;
        padding: 1.5rem;
        min-height: 350px;
    }
    .chart-wrapper canvas {
        max-height: 350px;
    }

    /* ─── Loading Overlay ──────────────────────────────── */
    .loading-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius);
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .loading-overlay.show {
        opacity: 1;
        pointer-events: all;
    }

    /* ─── Doughnut chart sizing ─────────────────────────── */
    .doughnut-wrapper {
        max-width: 280px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-graph-up-arrow me-2" style="color: var(--primary-light);"></i>
                Laporan Pemantauan
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-bar-chart-line me-1"></i>
                Statistik pengunjung antrian pelayanan
            </p>
        </div>
        <!-- <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-gradient">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </div> -->
    </div>

    {{-- Periode Filter --}}
    <div class="card-app p-3 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center px-2">
            <span class="fw-bold" style="color: var(--text-main);">
                <i class="bi bi-calendar-range me-2 text-primary"></i>
                Periode Laporan
            </span>
            <div class="periode-tabs mt-2 mt-sm-0">
                <button class="periode-btn active" data-periode="hari_ini" onclick="gantiPeriode('hari_ini', this)">
                    Harian
                </button>
                <button class="periode-btn" data-periode="mingguan" onclick="gantiPeriode('mingguan', this)">
                    Mingguan
                </button>
                <button class="periode-btn" data-periode="bulanan" onclick="gantiPeriode('bulanan', this)">
                    Bulanan
                </button>
            </div>
        </div>
    </div>

    {{-- Unduh Laporan Tahunan --}}
    <div class="card-app p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color: var(--text-main);">
            <i class="bi bi-download me-2 text-success"></i> Unduh Laporan Tahunan BPS
        </h5>
        <p class="text-muted mb-4" style="font-size: 0.85rem;">
            Pilih tahun untuk memfilter data kunjungan antrian BPS, lalu klik tombol unduh untuk mengekspor data lengkap ke dalam file berformat CSV yang kompatibel dengan Microsoft Excel.
        </p>
        <form action="{{ route('admin.laporan.download') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-sm-6 col-md-4">
                <label class="form-label fw-semibold">Pilih Tahun Laporan</label>
                <select name="tahun" class="form-select" style="height: auto; padding: 10px 16px; border-radius: 12px; border-color: var(--border-color); cursor: pointer;" required>
                    @php
                        $tahunSekarang = date('Y');
                        $tahunMulai = 2024;
                    @endphp
                    @for ($y = $tahunSekarang; $y >= $tahunMulai; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <button type="submit" class="btn btn-success w-100" style="border-radius: 12px; font-weight: 600; padding: 12.5px 20px; font-size: 0.95rem; background: #10B981; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Unduh Laporan (.CSV)
                </button>
            </div>
        </form>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-app ringkasan-card" style="border-top: 4px solid var(--primary); background: rgba(79, 70, 229, 0.03);">
                <div class="ringkasan-number" id="ringkasanTotal" style="color: var(--primary);">-</div>
                <div class="ringkasan-label">Total Pengunjung</div>
                <i class="bi bi-people ringkasan-icon-bg"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-app ringkasan-card" style="border-top: 4px solid var(--warning); background: rgba(245, 158, 11, 0.03);">
                <div class="ringkasan-number" id="ringkasanRataRata" style="color: var(--warning);">-</div>
                <div class="ringkasan-label">Rata-rata / Hari</div>
                <i class="bi bi-calendar-check ringkasan-icon-bg"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-app ringkasan-card" style="border-top: 4px solid var(--secondary); background: rgba(16, 185, 129, 0.03);">
                <div class="ringkasan-number" id="ringkasanTerramai" style="color: var(--secondary);">-</div>
                <div class="ringkasan-label">Hari Terramai</div>
                <i class="bi bi-graph-up-arrow ringkasan-icon-bg"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-app ringkasan-card" style="border-top: 4px solid var(--primary-light); background: rgba(129, 140, 248, 0.03);">
                <div class="ringkasan-number" id="ringkasanWaktu" style="color: var(--primary-light);">-</div>
                <div class="ringkasan-label">Avg. Waktu Layanan</div>
                <i class="bi bi-clock-history ringkasan-icon-bg"></i>
            </div>
        </div>
    </div>

    {{-- Grafik Pengunjung Harian --}}
    <div class="card-app mb-4" style="overflow: hidden; position: relative;">
        <div class="p-3 px-4" style="border-bottom: 1px solid var(--border-color); background: #fdfdfd;">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                Grafik Pengunjung
            </h6>
        </div>
        <div class="chart-wrapper" id="chartPengunjungWrapper">
            <canvas id="chartPengunjung"></canvas>
            <div class="loading-overlay" id="loadingOverlay">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted fw-medium">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row: Grafik Keperluan + Grafik Status --}}
    <div class="row g-3 mb-4">

        {{-- Grafik Keperluan (Stacked Bar) --}}
        <div class="col-lg-8">
            <div class="card-app" style="overflow: hidden; position: relative; height: 100%;">
                <div class="p-3 px-4" style="border-bottom: 1px solid var(--border-color); background: #fdfdfd;">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-layers-fill me-2 text-primary"></i>
                        Berdasarkan Keperluan
                    </h6>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartKeperluan"></canvas>
                </div>
            </div>
        </div>

        {{-- Grafik Distribusi Status (Doughnut) --}}
        <div class="col-lg-4">
            <div class="card-app d-flex flex-column" style="overflow: hidden; position: relative; height: 100%;">
                <div class="p-3 px-4" style="border-bottom: 1px solid var(--border-color); background: #fdfdfd;">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>
                        Distribusi Status
                    </h6>
                </div>
                <div class="doughnut-wrapper flex-grow-1 d-flex flex-column justify-content-center align-items-center">
                    <canvas id="chartStatus"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-3 pb-3 flex-wrap" id="statusLegend">
                    <small><span style="color: var(--success);">●</span> Selesai: <strong id="legendSelesai">0</strong></small>
                    <small><span style="color: var(--danger);">●</span> Dilewati: <strong id="legendDilewati">0</strong></small>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // ═══════════════════════════════════════════════════════════
    //  KONFIGURASI CHART.JS — Defaults
    // ═══════════════════════════════════════════════════════════
    Chart.defaults.color = '#475569'; // Slate 600 untuk teks cerah
    Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.05)';
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";

    // ═══════════════════════════════════════════════════════════
    //  VARIABEL CHART (agar bisa di-destroy & re-create)
    // ═══════════════════════════════════════════════════════════
    let chartPengunjung = null;
    let chartKeperluan  = null;
    let chartStatus     = null;
    let currentPeriode  = 'hari_ini';

    // ═══════════════════════════════════════════════════════════
    //  GANTI PERIODE
    // ═══════════════════════════════════════════════════════════
    function gantiPeriode(periode, btn) {
        currentPeriode = periode;

        // Active state
        document.querySelectorAll('.periode-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        // Fetch data baru
        fetchLaporan(periode);
    }

    // ═══════════════════════════════════════════════════════════
    //  FETCH DATA LAPORAN
    // ═══════════════════════════════════════════════════════════
    async function fetchLaporan(periode) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('show');

        try {
            // Gunakan URL absolut dengan timestamp untuk menghindari cache di hosting
            const timestamp = new Date().getTime();
            const url = `{{ url('/admin/api/laporan') }}?periode=${periode}&t=${timestamp}`;
            
            console.log('Fetching data from:', url);
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Fetch failed: ' + response.status);
            
            const data = await response.json();
            console.log('Data received:', data);

            if (data.labels && data.labels.length > 0) {
                updateRingkasan(data.ringkasan);
                renderChartPengunjung(data);
                renderChartKeperluan(data);
                renderChartStatus(data);
            } else {
                console.warn('No data found for this period');
                // Anda bisa menambahkan tampilan "Data Kosong" di sini jika perlu
            }

        } catch (error) {
            console.error('Laporan fetch error:', error);
            alert('Gagal mengambil data laporan. Silakan cek koneksi atau login kembali.');
        } finally {
            overlay.classList.remove('show');
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  UPDATE RINGKASAN
    // ═══════════════════════════════════════════════════════════
    function updateRingkasan(ringkasan) {
        animateNumber('ringkasanTotal', ringkasan.total);
        animateNumber('ringkasanRataRata', ringkasan.rataRataHarian);
        animateNumber('ringkasanTerramai', ringkasan.hariTerramai);

        const waktuEl = document.getElementById('ringkasanWaktu');
        if (ringkasan.avgWaktuLayanan === '-') {
            waktuEl.textContent = '-';
        } else {
            waktuEl.textContent = ringkasan.avgWaktuLayanan + ' min';
        }
    }

    function animateNumber(elementId, targetValue) {
        const el = document.getElementById(elementId);
        el.textContent = targetValue;
        el.style.transform = 'scale(1.1)';
        el.style.transition = 'transform 0.3s ease';
        setTimeout(() => { el.style.transform = 'scale(1)'; }, 300);
    }

    // ═══════════════════════════════════════════════════════════
    //  CHART 1: Pengunjung Harian (Bar + Line)
    // ═══════════════════════════════════════════════════════════
    function renderChartPengunjung(data) {
        if (chartPengunjung) chartPengunjung.destroy();

        const ctx = document.getElementById('chartPengunjung').getContext('2d');

        // Gradient fill untuk bar
        const gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.1)');

        chartPengunjung = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Total Pengunjung',
                        data: data.totalPengunjung,
                        backgroundColor: gradient,
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Tren',
                        data: data.totalPengunjung,
                        type: 'line',
                        borderColor: 'rgba(6, 182, 212, 0.8)',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#06b6d4',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        labels: { usePointStyle: true, padding: 20 }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        borderColor: 'rgba(79, 70, 229, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(148, 163, 184, 0.06)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  CHART 2: Berdasarkan Keperluan (Stacked Bar)
    // ═══════════════════════════════════════════════════════════
    function renderChartKeperluan(data) {
        if (chartKeperluan) chartKeperluan.destroy();

        const ctx = document.getElementById('chartKeperluan').getContext('2d');

        chartKeperluan = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Konsultasi',
                        data: data.konsultasi,
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Pengaduan',
                        data: data.pengaduan,
                        backgroundColor: 'rgba(204, 42, 58, 0.7)',
                        borderColor: '#cc2a3a',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { usePointStyle: true, padding: 16 }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        borderColor: 'rgba(6, 182, 212, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(148, 163, 184, 0.06)' }
                    },
                    x: {
                        stacked: true,
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  CHART 3: Distribusi Status (Doughnut)
    // ═══════════════════════════════════════════════════════════
    function renderChartStatus(data) {
        if (chartStatus) chartStatus.destroy();

        const totalSelesai  = data.selesai.reduce((a, b) => a + b, 0);
        const totalDilewati = data.dilewati.reduce((a, b) => a + b, 0);

        // Update legend
        document.getElementById('legendSelesai').textContent = totalSelesai;
        document.getElementById('legendDilewati').textContent = totalDilewati;

        const ctx = document.getElementById('chartStatus').getContext('2d');

        if (totalSelesai === 0 && totalDilewati === 0) {
            // No data — tampilkan empty state
            chartStatus = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Belum ada data'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['rgba(148, 163, 184, 0.1)'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    cutout: '70%',
                }
            });
            return;
        }

        chartStatus = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Dilewati'],
                datasets: [{
                    data: [totalSelesai, totalDilewati],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    borderWidth: 2,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const persen = total > 0 ? Math.round(context.raw / total * 100) : 0;
                                return context.label + ': ' + context.raw + ' (' + persen + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  INISIALISASI
    // ═══════════════════════════════════════════════════════════
    fetchLaporan('hari_ini');
</script>
@endpush
