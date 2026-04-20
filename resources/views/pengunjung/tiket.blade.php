{{-- ═══════════════════════════════════════════════════════════
     HALAMAN TIKET VIRTUAL & MONITOR REAL-TIME (Layar HP Pengunjung)
     Menggunakan AJAX Polling setiap 5 detik untuk update otomatis
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Tiket Antrian #' . $antrian->nomor_antrian)

@push('styles')
<style>
    /* ─── Tiket Number Display ─────────────────────────── */
    .nomor-antrian-display {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient-primary);
        margin: 0 auto;
        box-shadow: 0 0 50px rgba(79, 70, 229, 0.3);
        position: relative;
    }
    .nomor-antrian-display::before {
        content: '';
        position: absolute;
        width: 160px; height: 160px;
        border-radius: 50%;
        border: 2px solid rgba(79, 70, 229, 0.3);
        animation: pulse-ring-outer 2s ease-in-out infinite;
    }
    @keyframes pulse-ring-outer {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0; }
    }
    .nomor-antrian-value {
        font-size: 3.5rem;
        font-weight: 900;
        color: white;
        line-height: 1;
    }

    /* ─── Info Grid ────────────────────────────────────── */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 1.5rem;
    }
    .info-item {
        background: var(--dark-card);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-sm);
        padding: 1.25rem 1rem;
        text-align: center;
        transition: all 0.5s ease;
    }
    .info-item .info-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
        display: block;
    }
    .info-item .info-value {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .info-item .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-secondary);
        font-weight: 600;
    }

    /* ─── Status Banner ────────────────────────────────── */
    .status-banner {
        border-radius: var(--radius-sm);
        padding: 1rem;
        text-align: center;
        margin-top: 1.5rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.5s ease;
    }
    .status-menunggu {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #fbbf24;
    }
    .status-dipanggil {
        background: rgba(79, 70, 229, 0.15);
        border: 1px solid rgba(79, 70, 229, 0.4);
        color: #a5b4fc;
        animation: pulse-ring 1.5s ease-in-out infinite;
    }
    .status-selesai {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #6ee7b7;
    }
    .status-dilewati {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
    }

    /* ─── Tombol Antrian Baru ──────────────────────────── */
    .btn-antrian-baru {
        display: none;
        margin-top: 1.25rem;
        padding: 0.9rem 1.5rem;
        border: none;
        border-radius: var(--radius-sm);
        background: var(--gradient-primary);
        color: white;
        font-weight: 700;
        font-size: 1rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
    }
    .btn-antrian-baru:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 30px rgba(79, 70, 229, 0.45);
        color: white;
    }
    .btn-antrian-baru.show {
        display: block;
        animation: fadeSlideUp 0.5s ease forwards;
    }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ─── Live Indicator ───────────────────────────────── */
    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--success);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .live-dot {
        width: 8px; height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: blink 1.5s ease-in-out infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* ─── Highlight transition ─────────────────────────── */
    .value-changed {
        animation: highlight 1s ease;
    }
    @keyframes highlight {
        0% { transform: scale(1.15); color: #4f46e5; }
        100% { transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">

            {{-- Live Indicator --}}
            <div class="text-center mb-2">
                <span class="live-indicator">
                    <span class="live-dot"></span>
                    Live — Update Otomatis
                </span>
            </div>

            {{-- Notification Status --}}
            <div class="text-center mb-3">
                <div class="audio-banner" id="audioBanner" onclick="unlockAudio()" style="cursor: pointer;">
                    <i class="bi bi-bell-slash me-1"></i> Notifikasi suara belum aktif
                    <small class="d-block mt-1" style="opacity: 0.8; font-weight: normal;">Ketuk area ini untuk mengizinkan suara</small>
                </div>
            </div>

            {{-- Main Ticket Card --}}
            <div class="card-glass p-4" id="tiketCard">

                {{-- Keperluan Badge --}}
                <div class="text-center mb-3">
                    <div class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center">
                        <i class="bi bi-tag-fill me-2" style="color: var(--primary);"></i>
                        {{ $antrian->keperluan }}
                    </div>
                </div>

                {{-- Nomor Antrian Besar --}}
                <div class="text-center mb-2">
                    <p class="text-secondary mb-2" style="font-size: 0.85rem; font-weight: 500;">Nomor Antrian Anda</p>
                    <div class="nomor-antrian-display">
                        <span class="nomor-antrian-value">{{ $antrian->kode_antrian }}</span>
                    </div>
                    <p class="mt-3 mb-0 fw-semibold" style="font-size: 1.1rem;">{{ $antrian->nama }}</p>
                </div>

                {{-- 4 Info Boxes (Real-time via AJAX) --}}
                <div class="info-grid">

                    {{-- 1. Nomor Sedang Dipanggil --}}
                    <div class="info-item">
                        <span class="info-icon"><i class="bi bi-megaphone-fill text-primary"></i></span>
                        <div class="info-value" id="sedangDipanggil" style="color: var(--primary-light);">-</div>
                        <div class="info-label">Sedang Dipanggil</div>
                    </div>

                    {{-- 2. Nomor Antrian Anda --}}
                    <div class="info-item">
                        <span class="info-icon"><i class="bi bi-ticket-detailed-fill text-secondary"></i></span>
                        <div class="info-value" style="color: var(--secondary);">{{ $antrian->kode_antrian }}</div>
                        <div class="info-label">Nomor Anda</div>
                    </div>

                    {{-- 3. Sisa Antrian --}}
                    <div class="info-item">
                        <span class="info-icon"><i class="bi bi-people-fill text-danger"></i></span>
                        <div class="info-value" id="sisaAntrian" style="color: var(--accent);">...</div>
                        <div class="info-label">Sisa Antrian</div>
                    </div>

                    {{-- 4. Estimasi Waktu --}}
                    <div class="info-item">
                        <span class="info-icon"><i class="bi bi-stopwatch-fill text-success"></i></span>
                        <div class="info-value" id="estimasiWaktu" style="color: var(--success);">...</div>
                        <div class="info-label">Estimasi (Menit)</div>
                    </div>

                </div>

                {{-- Status Banner --}}
                <div class="status-banner status-menunggu" id="statusBanner">
                    <h5 class="mb-0 fw-bold" id="statusText">
                        <i class="bi bi-plus-circle-fill me-2"></i>
                        Menghubungkan ke server...
                    </h5>
                </div>

                {{-- Tombol Ambil Antrian Baru (muncul saat selesai/dilewati) --}}
                <a href="#" class="btn-antrian-baru" id="btnAntrianBaru" style="display: none;" onclick="ambilAntrianBaru(event)">
                    <i class="bi bi-plus-circle-fill me-2"></i>
                    Ambil Nomor Antrian Baru
                </a>

            </div>

            {{-- Footer Note --}}
            <div class="text-center mt-3">
                <small class="text-secondary">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Halaman ini update otomatis setiap 5 detik
                </small>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * ═══════════════════════════════════════════════════════════
     *  NOTIFIKASI SUARA — Web Audio API
     *  Generate beep tone secara programmatic (tanpa file audio)
     * ═══════════════════════════════════════════════════════════
     */
    const NotifikasiSuara = (function() {
        let audioCtx = null;
        let audioUnlocked = false;

        /**
         * Inisialisasi AudioContext (harus dipanggil dari user gesture).
         */
        function unlock() {
            if (audioUnlocked) return;
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                // Resume context jika suspended
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                // Play silent sound to unlock
                const buffer = audioCtx.createBuffer(1, 1, 22050);
                const source = audioCtx.createBufferSource();
                source.buffer = buffer;
                source.loop = true; // Loop audio kosong agar browser tetap hidup di background
                source.connect(audioCtx.destination);
                source.start(0);
                audioUnlocked = true;

                // Sembunyikan banner
                const banner = document.getElementById('audioBanner');
                if (banner) {
                    banner.style.animation = 'fadeSlideUp 0.3s ease reverse forwards';
                    setTimeout(() => banner.style.display = 'none', 300);
                }
                // Tampilkan status aktif
                const statusEl = document.getElementById('notifStatus');
                if (statusEl) {
                    statusEl.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Notifikasi Suara Aktif';
                    statusEl.style.color = 'var(--success)';
                }
            } catch(e) {
                console.warn('Audio unlock failed:', e);
            }
        }

        /**
         * Mainkan beep notification.
         * Pattern: 2 nada bergantian (ding-dong) selama 20 detik
         */
        function play() {
            if (!audioCtx || !audioUnlocked) return;

            const now = audioCtx.currentTime;
            const pattern = [];

            // 1 loop (2 nada) butuh 0.5 detik. Untuk 20 detik butuh 40 loop.
            for (let i = 0; i < 40; i++) {
                const startOff = i * 0.5;
                pattern.push({ freq: 880, start: startOff, duration: 0.15 });
                pattern.push({ freq: 1100, start: startOff + 0.2, duration: 0.15 });
            }

            pattern.forEach(note => {
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(note.freq, now + note.start);

                // Envelope: fade in + fade out supaya tidak klik
                gainNode.gain.setValueAtTime(0, now + note.start);
                gainNode.gain.linearRampToValueAtTime(0.5, now + note.start + 0.02);
                gainNode.gain.linearRampToValueAtTime(0, now + note.start + note.duration);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start(now + note.start);
                oscillator.stop(now + note.start + note.duration + 0.01);
            });
        }

        return { unlock, play, isUnlocked: () => audioUnlocked };
    })();

    /**
     * ═══════════════════════════════════════════════════════════
     *  PERSIST TIKET — Simpan ID tiket ke localStorage
     * ═══════════════════════════════════════════════════════════
     */
    (function() {
        const today = new Date().toISOString().slice(0, 10);
        localStorage.setItem('antrian_id', '{{ $antrian->id }}');
        localStorage.setItem('antrian_tanggal', today);
    })();

    /**
     * ═══════════════════════════════════════════════════════════
     *  AMBIL ANTRIAN BARU — Hapus localStorage dan redirect
     * ═══════════════════════════════════════════════════════════
     */
    function ambilAntrianBaru(e) {
        e.preventDefault();
        localStorage.removeItem('antrian_id');
        localStorage.removeItem('antrian_tanggal');
        window.location.href = '{{ route("antrian.daftar") }}';
    }

    /**
     * ═══════════════════════════════════════════════════════════
     *  UNLOCK AUDIO — Dipanggil dari tombol banner
     * ═══════════════════════════════════════════════════════════
     */
    function unlockAudio() {
        NotifikasiSuara.unlock();
    }

    /**
     * ═══════════════════════════════════════════════════════════
     *  AJAX POLLING — Real-time update tanpa refresh manual
     * ═══════════════════════════════════════════════════════════
     */
    (function() {
        'use strict';

        const ANTRIAN_ID = {{ $antrian->id }};
        const API_URL    = '/api/antrian/status/' + ANTRIAN_ID;
        const INTERVAL   = 5000;

        const elSedangDipanggil = document.getElementById('sedangDipanggil');
        const elSisaAntrian     = document.getElementById('sisaAntrian');
        const elEstimasiWaktu   = document.getElementById('estimasiWaktu');
        const elStatusBanner    = document.getElementById('statusBanner');
        const elStatusText      = document.getElementById('statusText');

        let prevData = {};

        async function fetchStatus() {
            try {
                const response = await fetch(API_URL);
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                updateUI(data);
            } catch (error) {
                console.error('Polling error:', error);
            }
        }

        function updateUI(data) {
            if (prevData.sedang_dipanggil !== data.sedang_dipanggil) {
                animateValue(elSedangDipanggil, data.sedang_dipanggil);
            } else {
                elSedangDipanggil.textContent = data.sedang_dipanggil;
            }

            if (prevData.sisa_antrian !== data.sisa_antrian) {
                animateValue(elSisaAntrian, data.sisa_antrian);
            } else {
                elSisaAntrian.textContent = data.sisa_antrian;
            }

            if (prevData.estimasi_menit !== data.estimasi_menit) {
                const estimasiText = data.estimasi_menit > 0
                    ? data.estimasi_menit + ' min'
                    : '0 min';
                animateValue(elEstimasiWaktu, estimasiText);
            } else {
                elEstimasiWaktu.textContent = data.estimasi_menit > 0
                    ? data.estimasi_menit + ' min'
                    : '0 min';
            }

            updateStatusBanner(data.status);

            const btnAntrianBaru = document.getElementById('btnAntrianBaru');
            if (data.status === 'selesai' || data.status === 'dilewati') {
                btnAntrianBaru.style.display = 'block';
                btnAntrianBaru.classList.add('show');
            } else {
                btnAntrianBaru.style.display = 'none';
                btnAntrianBaru.classList.remove('show');
            }

            prevData = { ...data };
        }

        function updateStatusBanner(status) {
            elStatusBanner.className = 'status-banner';

            const statusConfig = {
                'menunggu': {
                    class: 'status-menunggu',
                    icon: 'bi-hourglass-split',
                    text: 'Menunggu — Harap bersabar, antrian Anda akan segera dipanggil'
                },
                'dipanggil': {
                    class: 'status-dipanggil',
                    icon: 'bi-megaphone-fill',
                    text: 'GILIRAN ANDA! Silakan menuju loket pelayanan'
                },
                'selesai': {
                    class: 'status-selesai',
                    icon: 'bi-check-circle-fill',
                    text: 'Selesai — Terima kasih atas kunjungan Anda'
                },
                'dilewati': {
                    class: 'status-dilewati',
                    icon: 'bi-arrow-right-circle-fill',
                    text: 'Dilewati — Silakan hubungi petugas untuk info lebih lanjut'
                }
            };

            const config = statusConfig[status] || statusConfig['menunggu'];
            elStatusBanner.classList.add(config.class);
            elStatusText.innerHTML = '<i class="bi ' + config.icon + ' me-1"></i> ' + config.text;

            // 🔔 Notifikasi Dering + Getar saat dipanggil
            if (status === 'dipanggil' && prevData.status !== 'dipanggil') {
                // Bunyikan suara dering (20 detik)
                NotifikasiSuara.play();

                // Getarkan device selama 20 detik (pattern panjang-pause diulang)
                if (navigator.vibrate) {
                    const vibPattern = [];
                    for(let i=0; i<28; i++) {
                        vibPattern.push(500, 250);
                    }
                    navigator.vibrate(vibPattern);
                }
            } else if (status === 'selesai' || status === 'dilewati') {
                // Matikan getaran secara paksa jika status berubah sebelum dideringkan sepenuhnya
                if (navigator.vibrate) navigator.vibrate(0);
            }
        }

        function animateValue(element, newValue) {
            element.textContent = newValue;
            element.classList.add('value-changed');
            setTimeout(() => element.classList.remove('value-changed'), 1000);
        }

        // ─── Inisialisasi ──────────────────────────────────
        fetchStatus();
        let pollingTimer = setInterval(fetchStatus, INTERVAL);

        // Mencegah timer terhenti saat browser di-minimize / pindah tab
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                fetchStatus();
            }
        });

    })();
</script>
@endpush

