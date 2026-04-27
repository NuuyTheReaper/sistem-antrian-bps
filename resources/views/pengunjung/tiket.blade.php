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
{{-- NoSleep.js untuk mencegah layar mati --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/nosleep/0.12.0/NoSleep.min.js"></script>
<script>
    /**
     * ═══════════════════════════════════════════════════════════
     *  NOTIFIKASI SUARA — TTS + Web Audio API Ringtone
     * ═══════════════════════════════════════════════════════════
     */
    const NotifikasiSuara = (function() {
        let audioCtx = null;
        let audioUnlocked = false;
        let noSleep = new NoSleep();

        function unlock() {
            if (audioUnlocked) return;
            try {
                // AudioContext untuk standar & iOS
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                // Aktifkan NoSleep agar layar tidak mati (mencegah background freeze)
                noSleep.enable();

                // Play silent buffer untuk unlock
                const buffer = audioCtx.createBuffer(1, 1, 22050);
                const source = audioCtx.createBufferSource();
                source.buffer = buffer;
                source.connect(audioCtx.destination);
                source.start(0);
                
                audioUnlocked = true;

                // Update UI Banner
                const banner = document.getElementById('audioBanner');
                if (banner) {
                    banner.innerHTML = '<i class="bi bi-bell-fill text-success"></i> <span>Notifikasi Suara & Layanan Aktif</span>';
                    banner.style.background = '#ECFDF5';
                    setTimeout(() => banner.style.display = 'none', 3000);
                }
                console.log("Audio & NoSleep Unlocked");
            } catch(e) {
                console.error('Unlock failed:', e);
            }
        }

        function announceAndRing(kodeAntrian) {
            if (!audioUnlocked) return;

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(`Nomor antrian ${kodeAntrian.split('').join(' ')}, silakan menuju loket`);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                
                utterance.onend = () => playRingtone();
                utterance.onerror = () => playRingtone();
                window.speechSynthesis.speak(utterance);
            } else {
                playRingtone();
            }
        }

        function playRingtone() {
            if (!audioCtx || audioCtx.state === 'suspended') return;

            const now = audioCtx.currentTime;
            const DURASI = 15; 
            
            // Ding-dong pattern
            for (let i = 0; i < (DURASI * 2); i++) {
                const start = i * 0.5;
                playTone(880, now + start, 0.15);      // Nada 1
                playTone(1100, now + start + 0.2, 0.15); // Nada 2
            }
        }

        function playTone(freq, startTime, duration) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);
            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(0.3, startTime + 0.01);
            gain.gain.linearRampToValueAtTime(0, startTime + duration);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        }

        return { unlock, announceAndRing };
    })();

    function unlockAudio() {
        NotifikasiSuara.unlock();
    }

    // Registrasi Service Worker untuk Background Sync (Opsional tapi membantu)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(err => console.log("SW failed", err));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     *  AJAX POLLING WITH BACKGROUND RESILIENCE
     * ═══════════════════════════════════════════════════════════
     */
    (function() {
        const ANTRIAN_ID = {{ $antrian->id }};
        const KODE_ANTRIAN = '{{ $antrian->kode_antrian }}';
        const API_URL = '/api/antrian/status/' + ANTRIAN_ID;
        let prevStatus = '{{ $antrian->status }}';

        async function fetchStatus() {
            try {
                const res = await fetch(API_URL + '?t=' + Date.now());
                const data = await res.json();
                
                // Update UI mendasar
                document.getElementById('sedangDipanggil').textContent = data.sedang_dipanggil;
                document.getElementById('sisaAntrian').textContent = data.sisa_antrian;
                document.getElementById('statusText').innerHTML = data.status;

                // Jika status berubah jadi dipanggil
                if (data.status === 'dipanggil' && prevStatus !== 'dipanggil') {
                    NotifikasiSuara.announceAndRing(KODE_ANTRIAN);
                    // Vibrasi (Hanya Android)
                    if (navigator.vibrate) navigator.vibrate([500, 200, 500, 200, 500]);
                }
                prevStatus = data.status;
            } catch (e) { console.log("Polling error"); }
        }

        setInterval(fetchStatus, 5000);
        
        // Trigger saat tab dibuka kembali untuk memastikan data fresh
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) fetchStatus();
        });
    })();
</script>
@endpush
