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
        background: var(--dark-card);
        border: 1px solid var(--glass-border);
        color: var(--text-secondary);
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .periode-btn:hover {
        border-color: var(--primary);
        color: var(--primary-light);
    }
    .periode-btn.active {
        background: var(--gradient-primary);
        border-color: var(--primary);
        color: white;
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
        font-weight: 600;
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
<div class="container">

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
    <div class="card-glass p-3 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <span class="fw-bold" style="color: var(--text-primary);">
                <i class="bi bi-calendar-range me-1" style="color: var(--secondary);"></i>
                Periode
            </span>
            <div class="periode-tabs mt-2 mt-sm-0">
                <button class="periode-btn active" data-periode="7hari" onclick="gantiPeriode('7hari', this)">
                    7 Hari
                </button>
                <button class="periode-btn" data-periode="30hari" onclick="gantiPeriode('30hari', this)">
                    30 Hari
                </button>
                <button class="periode-btn" data-periode="bulan_ini" onclick="gantiPeriode('bulan_ini', this)">
                    Bulan Ini
                </button>
                <button class="periode-btn" data-periode="bulan_lalu" onclick="gantiPeriode('bulan_lalu', this)">
                    Bulan Lalu
                </button>
            </div>
        </div>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-glass ringkasan-card" style="border-top: 3px solid var(--primary-light);">
                <div class="ringkasan-number" id="ringkasanTotal" style="color: var(--primary-light);">-</div>
                <div class="ringkasan-label">Total Pengunjung</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-glass ringkasan-card" style="border-top: 3px solid var(--accent);">
                <div class="ringkasan-number" id="ringkasanRataRata" style="color: var(--accent);">-</div>
                <div class="ringkasan-label">Rata-rata / Hari</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-glass ringkasan-card" style="border-top: 3px solid var(--success);">
                <div class="ringkasan-number" id="ringkasanTerramai" style="color: var(--success);">-</div>
                <div class="ringkasan-label">Hari Terramai</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-glass ringkasan-card" style="border-top: 3px solid var(--secondary);">
                <div class="ringkasan-number" id="ringkasanWaktu" style="color: var(--secondary);">-</div>
                <div class="ringkasan-label">Avg. Waktu Layanan</div>
            </div>
        </div>
    </div>

    {{-- Grafik Pengunjung Harian --}}
    <div class="card-glass mb-4" style="overflow: hidden; position: relative;">
        <div class="p-3" style="border-bottom: 1px solid var(--glass-border);">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-bar-chart-fill me-2" style="color: var(--secondary);"></i>
                Grafik Pengunjung Harian
            </h5>
        </div>
        <div class="chart-wrapper" id="chartPengunjungWrapper">
            <canvas id="chartPengunjung"></canvas>
            <div class="loading-overlay" id="loadingOverlay">
                <div class="text-center">
                    <div class="spinner-border text-light mb-2" role="status"></div>
                    <div class="text-secondary">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row: Grafik Keperluan + Grafik Status --}}
    <div class="row g-3 mb-4">

        {{-- Grafik Keperluan (Stacked Bar) --}}
        <div class="col-lg-7">
            <div class="card-glass" style="overflow: hidden; position: relative; height: 100%;">
                <div class="p-3" style="border-bottom: 1px solid var(--glass-border);">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-layers-fill me-2" style="color: var(--secondary);"></i>
                        Berdasarkan Keperluan
                    </h5>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartKeperluan"></canvas>
                </div>
            </div>
        </div>

        {{-- Grafik Distribusi Status (Doughnut) --}}
        <div class="col-lg-5">
            <div class="card-glass" style="overflow: hidden; position: relative; height: 100%;">
                <div class="p-3" style="border-bottom: 1px solid var(--glass-border);">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart-fill me-2" style="color: var(--secondary);"></i>
                        Distribusi Status
                    </h5>
                </div>
                <div class="doughnut-wrapper">
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
    let currentPeriode  = '7hari';

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
            const response = await fetch('/admin/api/laporan?periode=' + periode);
            if (!response.ok) throw new Error('Fetch failed');
            const data = await response.json();

            updateRingkasan(data.ringkasan);
            renderChartPengunjung(data);
            renderChartKeperluan(data);
            renderChartStatus(data);

        } catch (error) {
            console.error('Laporan fetch error:', error);
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
                        backgroundColor: 'rgba(6, 182, 212, 0.7)',
                        borderColor: 'rgba(6, 182, 212, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Pengaduan',
                        data: data.pengaduan,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: 'rgba(245, 158, 11, 1)',
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
    fetchLaporan('7hari');
</script>
@endpush
