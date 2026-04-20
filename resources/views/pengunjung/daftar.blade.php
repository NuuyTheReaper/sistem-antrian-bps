{{-- ═══════════════════════════════════════════════════════════
     HALAMAN PENDAFTARAN ANTRIAN (Pengunjung via QR Code)
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Daftar Antrian - Pelayanan Publik')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- Header --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 70px; height: 70px; background: var(--gradient-primary);">
                    <i class="bi bi-person-plus-fill fs-2 text-white"></i>
                </div>
                <h1 class="h3 fw-bold mb-1">Pendaftaran Antrian</h1>
                <p class="text-secondary mb-0">Silakan isi data diri Anda untuk mendapatkan nomor antrian</p>
            </div>

            {{-- Form Card --}}
            <div class="card-glass p-4">
                <form action="{{ route('antrian.simpan') }}" method="POST" id="formDaftar">
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="nama" class="form-label form-label-custom">
                            <i class="bi bi-person me-1"></i> Nama Lengkap
                        </label>
                        <input type="text"
                               class="form-control form-control-custom @error('nama') is-invalid @enderror"
                               id="nama"
                               name="nama"
                               value="{{ old('nama') }}"
                               placeholder="Masukkan nama lengkap Anda"
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="mb-3">
                        <label for="alamat" class="form-label form-label-custom">
                            <i class="bi bi-geo-alt me-1"></i> Alamat
                        </label>
                        <textarea class="form-control form-control-custom @error('alamat') is-invalid @enderror"
                                  id="alamat"
                                  name="alamat"
                                  rows="3"
                                  placeholder="Masukkan alamat lengkap Anda"
                                  required>{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Keperluan --}}
                    <div class="mb-3">
                        <label for="keperluan" class="form-label form-label-custom">
                            <i class="bi bi-clipboard-check me-1"></i> Keperluan
                        </label>
                        <select class="form-select form-select-custom @error('keperluan') is-invalid @enderror"
                                id="keperluan"
                                name="keperluan"
                                required>
                            <option value="" disabled {{ old('keperluan') ? '' : 'selected' }}>— Pilih Keperluan —</option>
                            <option value="Konsultasi" {{ old('keperluan') == 'Konsultasi' ? 'selected' : '' }}>
                                1. Konsultasi
                            </option>
                            <option value="Pengaduan" {{ old('keperluan') == 'Pengaduan' ? 'selected' : '' }}>
                                2. Pengaduan
                            </option>
                        </select>
                        @error('keperluan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nomor HP --}}
                    <div class="mb-4">
                        <label for="nomor_hp" class="form-label form-label-custom">
                            <i class="bi bi-phone me-1"></i> Nomor HP
                        </label>
                        <input type="tel"
                               class="form-control form-control-custom @error('nomor_hp') is-invalid @enderror"
                               id="nomor_hp"
                               name="nomor_hp"
                               value="{{ old('nomor_hp') }}"
                               placeholder="Contoh: 08123456789"
                               required>
                        @error('nomor_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-gradient w-100 py-3" id="btnSubmit">
                        <i class="bi bi-send-fill me-2"></i>
                        Ambil Nomor Antrian
                    </button>
                </form>
            </div>

            {{-- Footer Info --}}
            <div class="text-center mt-3">
                <small class="text-secondary">
                    <i class="bi bi-shield-check me-1"></i>
                    Data Anda aman dan hanya digunakan untuk keperluan pelayanan
                </small>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * CEK TIKET EXISTING — Jika pengunjung sudah punya tiket hari ini,
     * langsung redirect ke halaman tiket (tidak perlu daftar ulang).
     * Data di localStorage akan expired otomatis saat tanggal berubah.
     */
    (function() {
        const savedId     = localStorage.getItem('antrian_id');
        const savedTanggal = localStorage.getItem('antrian_tanggal');
        const today        = new Date().toISOString().slice(0, 10);

        if (savedId && savedTanggal === today) {
            // Pengunjung sudah punya tiket hari ini → redirect
            window.location.href = '/antrian/tiket/' + savedId;
        } else {
            // Hapus data lama jika tanggal sudah berbeda (hari baru)
            localStorage.removeItem('antrian_id');
            localStorage.removeItem('antrian_tanggal');
        }
    })();

    // Disable double-submit
    document.getElementById('formDaftar').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    });
</script>
@endpush
