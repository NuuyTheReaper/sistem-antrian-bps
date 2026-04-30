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

    /* ─── Interaction Overlay (Modern Light Theme) ─── */
    #audioOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(241, 245, 249, 0.7); /* slate-100 */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 24px;
        text-align: center;
        color: #0f172a;
    }
    #audioOverlay::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset("images/logo-bps-no-text.png") }}');
        background-position: center;
        background-repeat: no-repeat;
        background-size: min(85vw, 450px);
        opacity: 0.9;
        pointer-events: none;
    }
    .overlay-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 28px;
        padding: 40px 32px;
        max-width: 420px;
        width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0,0,0,0.02) inset;
        animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .overlay-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }
    .icon-wrapper {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        position: relative;
        box-shadow: 0 0 30px rgba(245, 158, 11, 0.1);
    }
    .icon-wrapper::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid rgba(245, 158, 11, 0.3);
        animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulseRing {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .overlay-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 12px;
        background: linear-gradient(to right, var(--primary-dark, #1e3a8a), var(--primary, #3b82f6));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }
    .overlay-desc {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 32px;
    }
    .btn-unlock {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        padding: 16px 32px;
        border-radius: 16px;
        font-weight: 700;
        font-size: 1.05rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255,255,255,0.15) inset;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-unlock::after {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.25), transparent);
        transform: skewX(-20deg);
        animation: shimmer 3s infinite;
    }
    @keyframes shimmer {
        0% { left: -100%; }
        20% { left: 200%; }
        100% { left: 200%; }
    }
    .btn-unlock:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.3);
    }
    .btn-unlock:active { 
        transform: translateY(1px) scale(0.98); 
    }
    .apple-hint {
        display: flex;
        align-items: flex-start;
        text-align: left;
        gap: 12px;
        margin-top: 24px;
        padding: 14px;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 12px;
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.4;
    }
    .apple-hint i {
        color: #475569;
        font-size: 1.2rem;
        margin-top: -2px;
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>
@endpush

@section('content')
{{-- Overlay untuk iPhone / Android --}}
<div id="audioOverlay">
    <div class="overlay-card">
        <div class="icon-wrapper">
            <i class="bi bi-bell-fill text-warning" style="font-size: 2.5rem; filter: drop-shadow(0 4px 6px rgba(245,158,11,0.3));"></i>
        </div>
        <h3 class="overlay-title">Izinkan Notifikasi</h3>
        <p class="overlay-desc">Sistem memerlukan izin Anda untuk memutar suara panggilan antrian meskipun layar HP mati atau saat membuka aplikasi lain.</p>
        
        <button class="btn-unlock" onclick="unlockAllSystems()">
            <i class="bi bi-volume-up-fill fs-5"></i>
            <span>AKTIFKAN SEKARANG</span>
        </button>
        
        <div class="apple-hint">
            <i class="bi bi-apple"></i>
            <span><strong>Khusus iPhone:</strong> Pastikan Mode Hening (Silent Switch) di sisi samping HP dalam keadaan mati.</span>
        </div>
    </div>
</div>

<div class="container container-app">

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
            Monitoring Aktif BPS Tegal
        </small>
    </div>

</div>

{{-- Video Wake Lock Trick (Cara paling ampuh jaga browser tetap hidup di iOS) --}}
<video id="wakeLockVideo" loop muted playsinline style="display:none; width:1px; height:1px;">
    <source src="data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pbmYxbXA0MgAAAAhmcmVlAAAAAG1kYXQ=" type="video/mp4">
</video>

@endsection

@push('scripts')
<script>
    /**
     * ═══════════════════════════════════════════════════════════
     *  NOTIFIKASI SUARA & BACKGROUND MONITORING (Pro iOS Fix)
     * ═══════════════════════════════════════════════════════════
     */
    const NotifikasiSuara = (function() {
        let audioCtx = null;
        let audioUnlocked = false;
        const wakeLockVideo = document.getElementById('wakeLockVideo');

        function unlock() {
            if (audioUnlocked) return;
            
            try {
                // 1. AudioContext Prime
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();

                // 2. SpeechSynthesis Prime (WAJIB di iPhone saat klik)
                if ('speechSynthesis' in window) {
                    const silentSpeech = new SpeechSynthesisUtterance("");
                    window.speechSynthesis.speak(silentSpeech);
                }

                // 3. Video Wake Lock (Menjaga browser tidak suspend di iOS)
                wakeLockVideo.play().catch(e => console.log('Video WakeLock failed:', e));

                audioUnlocked = true;

                // Hilangkan Overlay
                document.getElementById('audioOverlay').style.display = 'none';
                
                // Minta izin notifikasi
                if ("Notification" in window) Notification.requestPermission();
                
            } catch(e) {
                console.error('Unlock error:', e);
            }
        }

        function announceAndRing(kodeAntrian) {
            if (!audioUnlocked) return;

            // Notifikasi Banner
            if (document.hidden && "Notification" in window && Notification.permission === "granted") {
                new Notification("Giliran Anda!", {
                    body: "Nomor " + kodeAntrian + " silakan menuju loket.",
                    icon: "/favicon.ico"
                });
            }

            // 1. TTS
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const teks = `Nomor antrian ${kodeAntrian.split('').join(' ')}, silakan menuju loket pelayanan`;
                const utterance = new SpeechSynthesisUtterance(teks);
                utterance.lang = 'id-ID';
                utterance.rate = 0.85;
                
                utterance.onend = () => playRingtone();
                utterance.onerror = () => playRingtone();
                
                window.speechSynthesis.speak(utterance);
            } else {
                playRingtone();
            }
        }

        function playRingtone() {
            if (!audioCtx) return;
            const now = audioCtx.currentTime;
            for (let i = 0; i < 30; i++) {
                const start = now + (i * 0.5);
                playTone(880, start, 0.15); // Ding
                playTone(660, start + 0.25, 0.15); // Dong
            }
        }

        function playTone(freq, start, dur) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, start);
            gain.gain.setValueAtTime(0, start);
            gain.gain.linearRampToValueAtTime(0.7, start + 0.02);
            gain.gain.linearRampToValueAtTime(0, start + dur);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(start);
            osc.stop(start + dur + 0.01);
        }

        return { unlock, announceAndRing };
    })();

    function unlockAllSystems() {
        NotifikasiSuara.unlock();
    }

    /**
     * ═══════════════════════════════════════════════════════════
     *  WEB WORKER POLLING (Robust Background)
     * ═══════════════════════════════════════════════════════════
     */
    (function() {
        const ANTRIAN_ID = {{ $antrian->id }};
        const KODE_ANTRIAN = '{{ $antrian->kode_antrian }}';
        const API_URL = window.location.origin + '/api/antrian/status/' + ANTRIAN_ID;
        
        const workerCode = `
            let timer = null;
            self.onmessage = function(e) {
                if (e.data === 'start') {
                    if (timer) clearInterval(timer);
                    timer = setInterval(() => {
                        fetch('${API_URL}')
                            .then(res => res.json())
                            .then(data => self.postMessage(data))
                            .catch(err => {});
                    }, 5000);
                }
            };
        `;
        
        const blob = new Blob([workerCode], { type: 'application/javascript' });
        const worker = new Worker(URL.createObjectURL(blob));
        let prevStatus = '{{ $antrian->status }}';

        worker.onmessage = (e) => updateUI(e.data);
        worker.postMessage('start');

        function updateUI(data) {
            if(!data) return;
            document.getElementById('sedangDipanggil').textContent = data.sedang_dipanggil;
            document.getElementById('sisaAntrian').textContent = data.sisa_antrian;
            document.getElementById('estimasiWaktu').textContent = data.estimasi_menit + ' min';
            
            const pct = Math.min(Math.round((data.sudah_dilayani / (data.total_antrian_keperluan || 1)) * 100), 100);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressLabel').textContent = data.sudah_dilayani + ' / ' + data.total_antrian_keperluan + ' dilayani';

            if (data.status === 'dipanggil' && prevStatus !== 'dipanggil') {
                NotifikasiSuara.announceAndRing(KODE_ANTRIAN);
            }
            
            updateStatusBanner(data.status);
            prevStatus = data.status;

            if (data.status === 'selesai' || data.status === 'dilewati') {
                document.getElementById('btnAntrianBaru').style.display = 'flex';
            }
        }

        function updateStatusBanner(status) {
            const el = document.getElementById('statusBanner');
            const txt = document.getElementById('statusText');
            el.className = 'status-banner status-' + status;
            const cfg = {
                'dipanggil': 'GILIRAN ANDA! Ke Loket',
                'selesai': 'Selesai dilayani',
                'dilewati': 'Antrian Dilewati',
                'menunggu': 'Menunggu antrian...'
            };
            txt.innerHTML = cfg[status] || cfg['menunggu'];
        }
    })();

    localStorage.setItem('antrian_id', '{{ $antrian->id }}');
    function ambilAntrianBaru(e) {
        e.preventDefault();
        localStorage.removeItem('antrian_id');
        window.location.href = '{{ route("antrian.daftar") }}';
    }
</script>
@endpush


