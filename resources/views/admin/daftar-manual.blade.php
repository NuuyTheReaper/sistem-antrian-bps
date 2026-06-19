@extends('layouts.app')

@section('title', 'Pendaftaran Manual - Admin')

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
        padding: 16px 8px;
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

    #kepStatistik:checked + .radio-card {
        background-color: #D1FAE5;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
    }
    #kepStatistik:checked + .radio-card i {
        color: #059669 !important;
    }
    #kepStatistik:checked + .radio-card .radio-title {
        color: #047857;
        font-weight: 700;
    }

    #kepPerpustakaan:checked + .radio-card {
        background-color: #FEF3C7;
        border-color: #D97706;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
    }
    #kepPerpustakaan:checked + .radio-card i {
        color: #D97706 !important;
    }
    #kepPerpustakaan:checked + .radio-card .radio-title {
        color: #B45309;
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
    
    <div class="mb-3 text-start px-2 mt-2 mt-md-0 w-100">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light" style="border-radius: 12px; font-weight: 600;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="ticket-card">
        <div class="ticket-header">
            <div class="hero-icon">
                <i class="bi bi-person-lines-fill text-white" style="font-size: 2.2rem;"></i>
            </div>
            <h2 class="fw-bold mb-2" style="letter-spacing: -0.5px;">Daftar Manual</h2>
            <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem; font-weight: 300;">Input data pengunjung yang datang tanpa smartphone</p>
        </div>

        <div class="ticket-body">
            <i class="bi bi-person-badge ticket-icon-bg"></i>
        <form action="{{ route('admin.simpan-manual') }}" method="POST" id="formManual">
            @csrf

            {{-- NIK --}}
            <div class="mb-4">
                <label class="form-label">Nomor Induk Kependudukan (NIK) (Opsional)</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-card-id"></i>
                    <input type="text" class="form-control @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik') }}" placeholder="16 digit NIK" minlength="16" maxlength="16">
                </div>
                @error('nik')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nama --}}
            <div class="mb-4">
                <label class="form-label">Nama Lengkap</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-person"></i>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama pengunjung" required>
                </div>
                @error('nama')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Jenis Kelamin --}}
            <div class="mb-4">
                <label class="form-label">Jenis Kelamin (Opsional)</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-gender-ambiguous"></i>
                    <select class="form-select @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin">
                        <option value="" selected>Pilih jenis kelamin</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                @error('jenis_kelamin')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Keperluan --}}
            <div class="mb-4">
                <label class="form-label">Keperluan Layanan</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepKonsultasi" value="Konsultasi" {{ old('keperluan') == 'Konsultasi' ? 'checked' : '' }} required>
                        <label class="radio-card w-100 h-100 d-flex flex-column align-items-center justify-content-center" for="kepKonsultasi">
                            <i class="bi bi-chat-dots-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.8rem;">Konsultasi</div>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepPengaduan" value="Pengaduan" {{ old('keperluan') == 'Pengaduan' ? 'checked' : '' }} required>
                        <label class="radio-card w-100 h-100 d-flex flex-column align-items-center justify-content-center" for="kepPengaduan">
                            <i class="bi bi-exclamation-octagon-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.8rem;">Pengaduan</div>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepStatistik" value="Rekomendasi Statistik" {{ old('keperluan') == 'Rekomendasi Statistik' ? 'checked' : '' }} required>
                        <label class="radio-card w-100 h-100 d-flex flex-column align-items-center justify-content-center" for="kepStatistik">
                            <i class="bi bi-bar-chart-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.8rem;">Rekomendasi Statistik</div>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check @error('keperluan') is-invalid @enderror" name="keperluan" id="kepPerpustakaan" value="Perpustakaan" {{ old('keperluan') == 'Perpustakaan' ? 'checked' : '' }} required>
                        <label class="radio-card w-100 h-100 d-flex flex-column align-items-center justify-content-center" for="kepPerpustakaan">
                            <i class="bi bi-book-fill fs-3 mb-2 d-block text-muted" style="transition: color 0.2s;"></i>
                            <div class="radio-title text-muted fw-semibold" style="font-size: 0.8rem;">Perpustakaan</div>
                        </label>
                    </div>
                </div>
                @error('keperluan')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nomor WhatsApp --}}
            <div class="mb-4">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-whatsapp"></i>
                    <input type="tel" class="form-control @error('nomor_hp') is-invalid @enderror" name="nomor_hp" value="{{ old('nomor_hp') }}" placeholder="Contoh: 0812..." maxlength="15" required>
                </div>
                @error('nomor_hp')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="form-label">Alamat Email (Opsional)</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-envelope"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Contoh: nama@domain.com">
                </div>
                @error('email')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Pekerjaan --}}
            <div class="mb-4">
                <label class="form-label">Pekerjaan (Opsional)</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-briefcase"></i>
                    <input type="text" class="form-control @error('pekerjaan') is-invalid @enderror" name="pekerjaan" value="{{ old('pekerjaan') }}" placeholder="Pekerjaan pengunjung">
                </div>
                @error('pekerjaan')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Pendidikan Terakhir --}}
            <div class="mb-4">
                <label class="form-label">Pendidikan Terakhir (Opsional)</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-mortarboard"></i>
                    <select class="form-select @error('pendidikan_terakhir') is-invalid @enderror" name="pendidikan_terakhir">
                        <option value="" selected>Pilih pendidikan terakhir</option>
                        <option value="SD" {{ old('pendidikan_terakhir') == 'SD' ? 'selected' : '' }}>SD / Sederajat</option>
                        <option value="SMP" {{ old('pendidikan_terakhir') == 'SMP' ? 'selected' : '' }}>SMP / Sederajat</option>
                        <option value="SMA" {{ old('pendidikan_terakhir') == 'SMA' ? 'selected' : '' }}>SMA / Sederajat</option>
                        <option value="Diploma" {{ old('pendidikan_terakhir') == 'Diploma' ? 'selected' : '' }}>Diploma (D1/D2/D3/D4)</option>
                        <option value="S1" {{ old('pendidikan_terakhir') == 'S1' ? 'selected' : '' }}>Sarjana (S1)</option>
                        <option value="S2" {{ old('pendidikan_terakhir') == 'S2' ? 'selected' : '' }}>Magister (S2)</option>
                        <option value="S3" {{ old('pendidikan_terakhir') == 'S3' ? 'selected' : '' }}>Doktor (S3)</option>
                    </select>
                </div>
                @error('pendidikan_terakhir')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Alamat --}}
            <div class="mb-4">
                <label class="form-label">Alamat Asal</label>
                <div class="form-icon-wrapper">
                    <i class="bi bi-geo-alt" style="top: 28px;"></i>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" rows="2" placeholder="Alamat asal pengunjung" required style="height: auto;">{{ old('alamat') }}</textarea>
                </div>
                @error('alamat')
                    <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-app w-100 mt-2" id="btnSubmitManual">
                <span>Daftarkan Pengunjung</span>
                <i class="bi bi-person-check-fill fs-5"></i>
            </button>
        </form>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('formManual').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitManual');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> <span>Memproses...</span>';
    });
</script>
@endpush
