{{-- ═══════════════════════════════════════════════════════════
     FORM PENDAFTARAN MANUAL OLEH PETUGAS
     Untuk pengunjung yang datang tanpa membawa smartphone
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Pendaftaran Manual - Admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- Back Button --}}
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-light rounded-pill mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>

            {{-- Header --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 70px; height: 70px; background: var(--gradient-warm);">
                    <i class="bi bi-pencil-square fs-2 text-white"></i>
                </div>
                <h1 class="h3 fw-bold mb-1">Pendaftaran Manual</h1>
                <p class="text-secondary mb-0">Input data pengunjung yang datang tanpa smartphone</p>
            </div>

            {{-- Form Card --}}
            <div class="card-glass p-4">
                <form action="{{ route('admin.simpan-manual') }}" method="POST" id="formManual">
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
                               placeholder="Masukkan nama pengunjung"
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
                                  placeholder="Masukkan alamat pengunjung"
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

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-gradient w-100 py-3" id="btnSubmitManual">
                        <i class="bi bi-person-check-fill me-2"></i>
                        Daftarkan Pengunjung
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('formManual').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitManual');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    });
</script>
@endpush
