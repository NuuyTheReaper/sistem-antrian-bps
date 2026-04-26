{{-- ═══════════════════════════════════════════════════════════
     HALAMAN TIKET VIRTUAL & MONITOR REAL-TIME (Layar HP Pengunjung)
     Menggunakan AJAX Polling setiap 5 detik untuk update otomatis
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Tiket Antrian #' . $antrian->nomor_antrian)

@push('styles')
<style>
    .container-app {
        max-width: 500px;
        margin: 0 auto;
        padding-bottom: 24px;
    }

    /* ─── Ticket Card (same structure as daftar.blade.php) ─── */
    .ticket-card {
        background: var(--app-surface);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }
    .ticket-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 30px 24px 40px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .ticket-header::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: -15px;
        width: 30px;
        height: 30px;
        background: var(--app-bg);
        border-radius: 50%;
    }
    .ticket-header::before {
        content: '';
        position: absolute;
        bottom: -15px;
        right: -15px;
        width: 30px;
        height: 30px;
        background: var(--app-bg);
        border-radius: 50%;
    }
    .ticket-body {
        padding: 30px 24px 24px;
        background: white;
        position: relative;
        overflow: hidden;
    }
    .ticket-icon-bg {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 6rem;
        opacity: 0.03;
        transform: rotate(-15deg);
        color: var(--primary);
        pointer-events: none;
    }

    /* ─── Hero Icon (matching daftar.blade.php) ─── */
    .hero-icon {
        width: 72px;
        height: 72px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: rotate(-5deg);
    }

    /* ─── Queue Number ─── */
    .queue-number {
        font-size: 5rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -2px;
    }

    /* ─── Info Row ─── */
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #F1F5F9;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-row .info-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .info-row .info-left .info-icon-sm {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .info-row .info-left .info-text {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-muted);
    }
    .info-row .info-right {
        font-size: 1.15rem;
        font-weight: 800;
        transition: all 0.3s ease;
    }

    /* ─── Status Banner ─── */
    .status-banner {
        border-radius: var(--radius-md);
        padding: 14px 16px;
        text-align: center;
        margin-top: 20px;
        transition: all 0.4s ease;
    }
    .status-menunggu {
        background: #FEF3C7;
        color: #D97706;
    }
    .status-dipanggil {
        background: #E0E7FF;
        color: #4338CA;
        animation: pulse-ring 2s infinite;
    }
    .status-selesai {
        background: #D1FAE5;
        color: #059669;
    }
    .status-dilewati {
        background: #FEE2E2;
        color: #DC2626;
    }

    /* ─── Audio Banner ─── */
    .audio-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 30px;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: var(--shadow-sm);
    }
    .audio-pill:hover {
        border-color: var(--primary-light);
        color: var(--primary);
    }

    /* ─── Value Change Animation ─── */
    .value-changed {
        animation: val-pop 0.4s ease;
    }
    @keyframes val-pop {
        0% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    /* ─── Fade Slide ─── */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="container container-app">

    {{-- Audio Permission Pill --}}
    <div class="text-center mb-3">
        <div class="audio-pill" id="audioBanner" onclick="unlockAudio()">
            <i class="bi bi-bell-slash text-danger"></i>
            <span>Ketuk untuk aktifkan suara notifikasi</span>
        </div>
    </div>

    {{-- Main Ticket Card --}}
    <div class="ticket-card" id="tiketCard">

        {{-- Header --}}
        <div class="ticket-header">
            <div class="hero-icon">
                <i class="bi bi-ticket-perforated text-white" style="font-size: 2.2rem;"></i>
            </div>

            <p style="font-size: 0.75rem; letter-spacing: 2px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; opacity: 0.85;">Nomor Antrian Anda</p>
            <h1 class="queue-number mb-0">{{ $antrian->kode_antrian }}</h1>

            <hr style="border-top: 2px dashed rgba(255,255,255,0.25); opacity: 1; margin: 24px 0 20px;">

            <div class="d-flex justify-content-between text-start" style="padding: 0 4px;">
                <div>
                    <div style="font-size: 0.7rem; letter-spacing: 1.5px; font-weight: 600; text-transform: uppercase; opacity: 0.7; margin-bottom: 4px;">Nama</div>
                    <div style="font-weight: 800; font-size: 1.1rem;">{{ $antrian->nama }}</div>
                </div>
                <div class="text-end">
                    <div style="font-size: 0.7rem; letter-spacing: 1.5px; font-weight: 600; text-transform: uppercase; opacity: 0.7; margin-bottom: 4px;">Layanan</div>
                    <div style="font-weight: 800; font-size: 1.1rem;">{{ $antrian->keperluan }}</div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="ticket-body">
            <i class="bi bi-people ticket-icon-bg"></i>

            {{-- Info Rows --}}
            <div class="info-row">
                <div class="info-left">
                    <div class="info-icon-sm" style="background: #EEF2FF; color: var(--primary);">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <span class="info-text">Sedang Dipanggil</span>
                </div>
                <div class="info-right" id="sedangDipanggil" style="color: var(--primary);">-</div>
            </div>

            <div class="info-row">
                <div class="info-left">
                    <div class="info-icon-sm" style="background: #FEF3C7; color: #D97706;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="info-text">Sisa Antrian</span>
                </div>
                <div class="info-right" id="sisaAntrian" style="color: #D97706;">...</div>
            </div>

            <div class="info-row">
                <div class="info-left">
                    <div class="info-icon-sm" style="background: #D1FAE5; color: #059669;">
                        <i class="bi bi-stopwatch-fill"></i>
                    </div>
                    <span class="info-text">Estimasi Waktu</span>
                </div>
                <div class="info-right" id="estimasiWaktu" style="color: #059669;">...</div>
            </div>

            {{-- Progress Bar --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Progres Antrian</span>
                    <span id="progressLabel" style="font-size: 0.8rem; font-weight: 700; color: var(--primary);">0 / 0</span>
                </div>
                <div style="height: 8px; background: #F1F5F9; border-radius: 10px; overflow: hidden;">
                    <div id="progressBar" style="height: 100%; width: 0%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; transition: width 0.6s ease;"></div>
                </div>
            </div>

            {{-- Status Banner --}}
            <div class="status-banner status-menunggu" id="statusBanner">
                <span class="fw-bold" id="statusText" style="font-size: 0.9rem;">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Menghubungkan...
                </span>
            </div>

            {{-- Tombol Ambil Antrian Baru --}}
            <a href="#" class="btn-app w-100 mt-3" id="btnAntrianBaru" onclick="ambilAntrianBaru(event)" style="display: none; text-decoration: none;">
                <span>Ambil Antrian Baru</span>
                <i class="bi bi-arrow-right fs-5"></i>
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-4">
        <small class="text-muted" style="font-weight: 500;">
            <i class="bi bi-shield-check me-1 text-success"></i>
            Data Anda aman dan terenkripsi
        </small>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /**
     * ═══════════════════════════════════════════════════════════
     *  NOTIFIKASI SUARA — TTS + Web Audio API Ringtone
     *  1. Mengucapkan "Nomor antrian K-024, silakan menuju loket"
     *  2. Setelah TTS selesai → dering otomatis 15 detik
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
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                // Play silent sound to unlock
                const buffer = audioCtx.createBuffer(1, 1, 22050);
                const source = audioCtx.createBufferSource();
                source.buffer = buffer;
                source.loop = true;
                source.connect(audioCtx.destination);
                source.start(0);
                audioUnlocked = true;

                // Update banner UI
                const banner = document.getElementById('audioBanner');
                if (banner) {
                    banner.innerHTML = '<i class="bi bi-bell-fill text-success"></i> <span>Notifikasi suara aktif</span>';
                    banner.style.borderColor = '#A7F3D0';
                    banner.style.background = '#ECFDF5';
                    setTimeout(() => {
                        banner.style.animation = 'fadeSlideUp 0.3s ease reverse forwards';
                        setTimeout(() => banner.style.display = 'none', 300);
                    }, 2000);
                }
            } catch(e) {
                console.warn('Audio unlock failed:', e);
            }
        }

        /**
         * Ucapkan nomor antrian menggunakan SpeechSynthesis API,
         * lalu mainkan dering 15 detik setelah TTS selesai.
         */
        function announceAndRing(kodeAntrian) {
            if (!audioCtx || !audioUnlocked) {
                // Fallback: langsung dering tanpa TTS
                playRingtone();
                return;
            }

            // Coba TTS dulu
            if ('speechSynthesis' in window) {
                // Cancel any pending speech
                window.speechSynthesis.cancel();

                const teks = `Nomor antrian ${kodeAntrian.split('').join(' ')}, silakan menuju loket pelayanan`;
                const utterance = new SpeechSynthesisUtterance(teks);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;

                // Pilih voice Indonesia jika tersedia
                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(v => v.lang.startsWith('id'));
                if (idVoice) utterance.voice = idVoice;

                utterance.onend = function() {
                    // Setelah TTS selesai → dering 15 detik
                    setTimeout(() => playRingtone(), 500);
                };
                utterance.onerror = function() {
                    // Fallback jika TTS gagal
                    playRingtone();
                };

                window.speechSynthesis.speak(utterance);
            } else {
                // Browser tidak support TTS → langsung dering
                playRingtone();
            }
        }

        /**
         * Mainkan nada dering 15 detik.
         * Pattern: 2 nada bergantian (ding-dong)
         */
        function playRingtone() {
            if (!audioCtx || !audioUnlocked) return;

            const now = audioCtx.currentTime;
            const DURASI_DETIK = 15;
            const pattern = [];

            // 1 loop (2 nada) butuh 0.5 detik. Untuk 15 detik butuh 30 loop.
            for (let i = 0; i < (DURASI_DETIK * 2); i++) {
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

        return { unlock, announceAndRing, playRingtone, isUnlocked: () => audioUnlocked };
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
     *  Preload voices (beberapa browser memuat voices secara async)
     * ═══════════════════════════════════════════════════════════
     */
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
    }

    /**
     * ═══════════════════════════════════════════════════════════
     *  AJAX POLLING — Real-time update tanpa refresh manual
     * ═══════════════════════════════════════════════════════════
     */
    (function() {
        'use strict';

        const ANTRIAN_ID = {{ $antrian->id }};
        const KODE_ANTRIAN = '{{ $antrian->kode_antrian }}';
        const API_URL    = '/api/antrian/status/' + ANTRIAN_ID;
        const INTERVAL   = 5000;

        const elSedangDipanggil = document.getElementById('sedangDipanggil');
        const elSisaAntrian     = document.getElementById('sisaAntrian');
        const elEstimasiWaktu   = document.getElementById('estimasiWaktu');
        const elStatusBanner    = document.getElementById('statusBanner');
        const elStatusText      = document.getElementById('statusText');
        const elProgressBar     = document.getElementById('progressBar');
        const elProgressLabel   = document.getElementById('progressLabel');

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
            // Update info rows
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

            const estimasiText = data.estimasi_menit > 0
                ? data.estimasi_menit + ' min' : '0 min';
            if (prevData.estimasi_menit !== data.estimasi_menit) {
                animateValue(elEstimasiWaktu, estimasiText);
            } else {
                elEstimasiWaktu.textContent = estimasiText;
            }

            // Update progress bar
            updateProgressBar(data);

            // Update status banner
            updateStatusBanner(data.status);

            // Show/hide button
            const btnAntrianBaru = document.getElementById('btnAntrianBaru');
            if (data.status === 'selesai' || data.status === 'dilewati') {
                btnAntrianBaru.style.display = 'flex';
                btnAntrianBaru.style.animation = 'fadeSlideUp 0.5s ease forwards';
            } else {
                btnAntrianBaru.style.display = 'none';
            }

            prevData = { ...data };
        }

        function updateProgressBar(data) {
            const total = data.total_antrian_keperluan || 1;
            const served = data.sudah_dilayani || 0;
            const pct = Math.min(Math.round((served / total) * 100), 100);

            elProgressBar.style.width = pct + '%';
            elProgressLabel.textContent = served + ' / ' + total + ' dilayani';

            // Warna hijau jika sudah selesai/dipanggil
            if (data.status === 'selesai' || data.status === 'dipanggil') {
                elProgressBar.style.background = 'linear-gradient(135deg, #059669, #10B981)';
            } else {
                elProgressBar.style.background = 'linear-gradient(135deg, var(--primary), var(--primary-light))';
            }
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

            // 🔔 TTS Announcement + Dering 15 detik saat dipanggil
            if (status === 'dipanggil' && prevData.status !== 'dipanggil') {
                // Ucapkan nomor antrian, lalu otomatis dering setelahnya
                NotifikasiSuara.announceAndRing(KODE_ANTRIAN);

                // Getarkan device selama 15 detik
                if (navigator.vibrate) {
                    const vibPattern = [];
                    for(let i = 0; i < 20; i++) {
                        vibPattern.push(500, 250);
                    }
                    navigator.vibrate(vibPattern);
                }
            } else if (status === 'selesai' || status === 'dilewati') {
                if (navigator.vibrate) navigator.vibrate(0);
                if ('speechSynthesis' in window) window.speechSynthesis.cancel();
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
