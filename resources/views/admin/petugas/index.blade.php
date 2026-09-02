@extends('layouts.app')

@section('title', 'Kelola Petugas - Sistem Antrian')

@push('styles')
<style>
    .card-petugas {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .card-petugas:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .avatar-circle {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
    }

    .form-icon-wrapper {
        position: relative;
    }
    .form-icon-wrapper .bi {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-size: 1.1rem;
    }
    .form-icon-wrapper .form-control {
        padding-left: 48px;
        height: 48px;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1000px;">



    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-people-fill me-2" style="color: var(--primary-light);"></i>
                Kelola Petugas
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-shield-lock-fill me-1 text-info"></i>
                Hanya Administrator yang dapat menambahkan, mengedit, atau menghapus akun Petugas.
            </p>
        </div>
        <div class="mt-3 mt-md-0 w-100 w-md-auto">
            <button class="btn btn-gradient py-2 px-4 w-100" style="border-radius: var(--radius-lg); font-weight: 600;" data-bs-toggle="modal" data-bs-target="#tambahPetugasModal">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Petugas
            </button>
        </div>
    </div>

    {{-- Petugas List --}}
    <div class="row g-3">
        @forelse($petugasList as $item)
            <div class="col-md-6">
                <div class="card-app card-petugas p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($item->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-main); font-size: 1.05rem;">{{ $item->name }}</h5>
                            <p class="text-secondary mb-1" style="font-size: 0.85rem;"><i class="bi bi-envelope me-1"></i>{{ $item->email }}</p>
                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                <i class="bi bi-person-badge-fill me-1 text-primary"></i>{{ $item->role === 'kepala_bps' ? 'Kepala BPS' : 'Petugas Pelayanan' }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button class="btn btn-outline-primary btn-sm px-3" style="border-radius: 10px; font-weight: 600;"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <form action="{{ route('admin.petugas.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3 w-100" style="border-radius: 10px; font-weight: 600;">
                                <i class="bi bi-trash3 me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Edit Petugas --}}
            <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                        <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px;">
                            <h5 class="modal-title fw-bold" id="editModalLabel{{ $item->id }}">
                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Akun Petugas
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('admin.petugas.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body" style="padding: 24px;">
                                {{-- Nama --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama Lengkap</label>
                                    <div class="form-icon-wrapper">
                                        <i class="bi bi-person"></i>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $item->name) }}" required>
                                    </div>
                                </div>
                                {{-- Email --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Alamat Email</label>
                                    <div class="form-icon-wrapper">
                                        <i class="bi bi-envelope"></i>
                                        <input type="email" class="form-control" name="email" value="{{ old('email', $item->email) }}" required>
                                    </div>
                                </div>
                                {{-- Role --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Hak Akses (Role)</label>
                                    <div class="form-icon-wrapper">
                                        <i class="bi bi-shield-check"></i>
                                        <select name="role" class="form-select form-control" required style="padding-left: 48px; height: 48px; border-radius: 12px; cursor: pointer;">
                                            <option value="petugas" {{ (old('role', $item->role) == 'petugas') ? 'selected' : '' }}>Petugas Pelayanan</option>
                                            <option value="kepala_bps" {{ (old('role', $item->role) == 'kepala_bps') ? 'selected' : '' }}>Kepala BPS</option>
                                        </select>
                                    </div>
                                </div>
                                <hr class="my-4 text-muted">
                                <p class="text-muted" style="font-size: 0.8rem;"><i class="bi bi-info-circle me-1"></i>Isi kolom password di bawah jika ingin mengubah password petugas, atau biarkan kosong jika tidak ingin diubah.</p>
                                {{-- Password Baru --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Password Baru (Opsional)</label>
                                    <div class="form-icon-wrapper">
                                        <i class="bi bi-lock"></i>
                                        <input type="password" class="form-control" name="password" placeholder="Masukkan password baru">
                                    </div>
                                </div>
                                {{-- Konfirmasi Password --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                    <div class="form-icon-wrapper">
                                        <i class="bi bi-shield-lock"></i>
                                        <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 20px;">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem;">Batal</button>
                                <button type="submit" class="btn btn-primary" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: var(--primary); border: none; padding: 8px 20px;">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-secondary">
                <i class="bi bi-people fs-1 mb-2 d-block opacity-50"></i>
                <span class="fw-semibold">Belum ada akun petugas terdaftar. Silakan tambah petugas baru.</span>
            </div>
        @endforelse
    </div>

</div>

{{-- Modal Tambah Petugas --}}
<div class="modal fade" id="tambahPetugasModal" tabindex="-1" aria-labelledby="tambahPetugasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px;">
                <h5 class="modal-title fw-bold" id="tambahPetugasModalLabel">
                    <i class="bi bi-person-plus text-primary me-2"></i> Tambah Akun Petugas Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.petugas.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <div class="form-icon-wrapper">
                            <i class="bi bi-person"></i>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap petugas" required>
                        </div>
                        @error('name')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Email</label>
                        <div class="form-icon-wrapper">
                            <i class="bi bi-envelope"></i>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Masukkan email petugas" required>
                        </div>
                        @error('email')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Role --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hak Akses (Role)</label>
                        <div class="form-icon-wrapper">
                            <i class="bi bi-shield-check"></i>
                            <select name="role" class="form-select form-control @error('role') is-invalid @enderror" required style="padding-left: 48px; height: 48px; border-radius: 12px; cursor: pointer;">
                                <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>Petugas Pelayanan</option>
                                <option value="kepala_bps" {{ old('role') == 'kepala_bps' ? 'selected' : '' }}>Kepala BPS</option>
                            </select>
                        </div>
                        @error('role')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="form-icon-wrapper">
                            <i class="bi bi-lock"></i>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Konfirmasi Password --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password</label>
                        <div class="form-icon-wrapper">
                            <i class="bi bi-shield-lock"></i>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem;">Batal</button>
                    <button type="submit" class="btn btn-gradient" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; border: none; padding: 10px 24px;">Tambah Petugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
