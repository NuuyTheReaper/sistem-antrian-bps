{{-- ═══════════════════════════════════════════════════════════
     HALAMAN LOGIN ADMIN
     Form autentikasi untuk petugas/resepsionis
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Login Admin - Sistem Antrian')

@push('styles')
<style>
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .login-container {
        display: flex;
        width: 1000px;
        max-width: 100%;
        background: white;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    }
    .login-side-info {
        flex: 1;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 60px 40px;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .login-side-info::before {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        top: -100px; right: -100px;
    }
    .login-side-form {
        flex: 1;
        padding: 60px 50px;
        background: white;
    }
    .login-logo-circle {
        width: 100px; height: 100px;
        background: white;
        border-radius: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transform: rotate(-5deg);
    }
    .form-control-login {
        height: 54px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        padding: 0 20px 0 45px;
        transition: all 0.3s;
    }
    .form-control-login:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.1rem;
    }
    
    @media (max-width: 991px) {
        .login-side-info { display: none; }
        .login-container { max-width: 450px; }
    }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <div class="login-container">
        
        {{-- Side Info (Left) --}}
        <div class="login-side-info">
            <div class="login-logo-circle">
                <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS" style="width: 70%;">
            </div>
            <h2 class="fw-bold mb-3">Sistem Antrian Cerdas</h2>
            <p class="opacity-75 fw-light">Badan Pusat Statistik Kota Tegal</p>
            <div class="mt-4 p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10">
                <small class="d-block mb-1 opacity-75">BPS Melayani dengan Hati</small>
                <div class="fw-bold">#DataMencerdaskanBangsa</div>
            </div>
        </div>

        {{-- Side Form (Right) --}}
        <div class="login-side-form">
            <div class="mb-5">
                <h3 class="fw-bold text-dark mb-2">Selamat Datang</h3>
                <p class="text-muted">Silakan masuk untuk mengelola antrian</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 d-flex align-items-center" style="background: rgba(239, 68, 68, 0.08); color: #DC2626;">
                    <i class="bi bi-exclamation-circle-fill me-3 fs-4"></i>
                    <small class="fw-medium">{{ $errors->first() }}</small>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="formLogin">
                @csrf

                <div class="mb-4">
                    <label class="form-label text-dark fw-semibold small">Email Address</label>
                    <div class="position-relative">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control form-control-login" placeholder="admin@bps.go.id" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-dark fw-semibold small">Password</label>
                    <div class="position-relative">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control form-control-login" placeholder="••••••••" required>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted pe-3" onclick="togglePassword()" style="text-decoration: none;">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label text-muted small" for="remember">Ingat Saya</label>
                    </div>
                    <!-- <a href="#" class="text-primary small fw-bold text-decoration-none">Lupa Password?</a> -->
                </div>

                <button type="submit" class="btn-app w-100 py-3 rounded-4 shadow-sm" id="btnLogin">
                    <span>Masuk ke Panel</span>
                    <i class="bi bi-arrow-right-short fs-4"></i>
                </button>
            </form>

            <div class="text-center mt-5 pt-4 border-top border-light">
                <p class="text-muted small">Butuh bantuan? <a href="#" class="text-primary fw-bold text-decoration-none">Hubungi IT Support</a></p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Disable double-submit
    document.getElementById('formLogin').addEventListener('submit', function() {
        const btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    });
</script>
@endpush
