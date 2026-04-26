@extends('layouts.app')

@section('title', 'Ambil Antrian - BPS Tegal')

@push('styles')
<style>
    .form-icon-wrapper {
        position: relative;
    }
    .form-icon-wrapper .bi {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-size: 1.2rem;
    }
    .form-icon-wrapper .form-control, .form-icon-wrapper .form-select {
        padding-left: 54px;
        height: 56px;
    }
    .form-icon-wrapper textarea.form-control {
        padding-top: 16px;
    }
    
    .hero-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius-xl);
        padding: 32px 24px;
        text-align: center;
        color: white;
        margin-bottom: -30px;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .radio-card {
        border: 2px solid transparent;
        border-radius: var(--radius-md);
        background-color: #F1F5F9;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        padding: 16px;
    }
    .radio-card:hover {
        background-color: #E2E8F0;
    }
    #kepKonsultasi:checked + .radio-card {
        background-color: #EEF2FF;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }
    #kepKonsultasi:checked + .radio-card i {
        color: var(--primary) !important;
    }
    #kepKonsultasi:checked + .radio-card .radio-title {
        color: var(--primary-dark);
        font-weight: 700;
    }

    #kepPengaduan:checked + .radio-card {
        background-color: #FEE2E2;
        border-color: #cc2a3a;
        box-shadow: 0 4px 12px rgba(204, 42, 58, 0.15);
    }
    #kepPengaduan:checked + .radio-card i {
        color: #cc2a3a !important;
    }
    #kepPengaduan:checked + .radio-card .radio-title {
        color: #991b2b;
        font-weight: 700;
    }
    
    /* ─── Modern Ticket Design ─────────────────────────── */
    .ticket-card {
        background: var(--app-surface);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
        margin-top: 10px;
    }
    
    .ticket-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 30px 20px;
        text-align: center;
        color: white;
        position: relative;
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
    


    .container-app {
        max-width: 500px;
        margin: 0 auto;
        padding-bottom: 24px;
    }
</style>
@endpush

@section('content')
<div class="container container-app">
    
    <div class="ticket-card">
        <div class="ticket-header">
            <div class="hero-icon">
                <i class="bi bi-ticket-perforated text-white" style="font-size: 2.2rem;"></i>
            </div>
            <h2 class="fw-bold mb-2" style="letter-spacing: -0.5px;">Ambil Antrian</h2>
            <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem; font-weight: 300;">Dapatkan nomor antrian Anda secara digital untuk pelayanan yang lebih cepat.</p>
        </div>

        <div class="ticket-body">
            <i class="bi bi-person-plus ticket-icon-bg"></i>
        <form action="{{ route('antrian.simpan') }}" method="POST" id="formDaftar">
            @csrf

            <div class="mb-4">
                <label class="form-label">Nama Lengkap</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-person"></i>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama Anda" required>
                </div>
                @error('nama')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Keperluan Layanan</label>
                <div class="row g-3">
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepKonsultasi" value="Konsultasi" {{ old('keperluan') == 'Konsultasi' ? 'checked' : '' }} required>
                        <label class="radio-card w-100" for="kepKonsultasi">
                            <i class="bi bi-chat-dots-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.9rem;">Konsultasi</div>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepPengaduan" value="Pengaduan" {{ old('keperluan') == 'Pengaduan' ? 'checked' : '' }} required>
                        <label class="radio-card w-100" for="kepPengaduan">
                            <i class="bi bi-exclamation-octagon-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.9rem;">Pengaduan</div>
                        </label>
                    </div>
                </div>
                @error('keperluan')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-whatsapp"></i>
                    <input type="tel" class="form-control @error('nomor_hp') is-invalid @enderror" name="nomor_hp" value="{{ old('nomor_hp') }}" placeholder="Contoh: 0812..." required>
                </div>
                @error('nomor_hp')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Alamat</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-geo-alt" style="top: 28px;"></i>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" rows="2" placeholder="Alamat asal" required style="height: auto;">{{ old('alamat') }}</textarea>
                </div>
                @error('alamat')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-app w-100 mt-2" id="btnSubmit">
                <span>Dapatkan Nomor</span>
                <i class="bi bi-arrow-right fs-5"></i>
            </button>
        </form>
    </div>
    </div>
    
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
    (function() {
        const savedId     = localStorage.getItem('antrian_id');
        const savedTanggal = localStorage.getItem('antrian_tanggal');
        const today        = new Date().toISOString().slice(0, 10);

        if (savedId && savedTanggal === today) {
            window.location.href = '/antrian/tiket/' + savedId;
        } else {
            localStorage.removeItem('antrian_id');
            localStorage.removeItem('antrian_tanggal');
        }
    })();

    document.getElementById('formDaftar').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> <span>Memproses...</span>';
    });
</script>
@endpush
