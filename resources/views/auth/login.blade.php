{{-- ═══════════════════════════════════════════════════════════
     HALAMAN LOGIN ADMIN
     Form autentikasi untuk petugas/resepsionis
     ═══════════════════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Login Admin - Sistem Antrian')

@push('styles')
<style>
    .login-card {
        max-width: 420px;
        margin: 0 auto;
    }
    .login-icon-wrap {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient-primary);
        margin: 0 auto 1.5rem;
        box-shadow: 0 0 50px rgba(79, 70, 229, 0.3);
        position: relative;
    }
    .login-icon-wrap::before {
        content: '';
        position: absolute;
        width: 110px; height: 110px;
        border-radius: 50%;
        border: 2px solid rgba(79, 70, 229, 0.3);
        animation: pulse-ring-login 2s ease-in-out infinite;
    }
    @keyframes pulse-ring-login {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0; }
    }
    .login-icon-wrap i {
        font-size: 2.2rem;
        color: white;
    }
    .login-divider {
        height: 1px;
        background: var(--glass-border);
        margin: 1.5rem 0;
    }
    .form-check-custom .form-check-input {
        background-color: var(--dark-card);
        border-color: var(--glass-border);
    }
    .form-check-custom .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .form-check-custom .form-check-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    .error-alert {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: var(--radius-sm);
        color: #fca5a5;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .error-alert i {
        color: var(--danger);
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
        <div class="col-md-6 col-lg-5">

            <div class="login-card">
                {{-- Icon --}}
                <div class="text-center">
                    <div class="login-icon-wrap">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h1 class="h3 fw-bold mb-1">Login Admin</h1>
                    <p class="text-secondary mb-0">Masuk untuk mengelola antrian pelayanan</p>
                </div>

                <div class="login-divider"></div>

                {{-- Error Alert --}}
                @if($errors->any())
                    <div class="error-alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Login Form --}}
                <div class="card-glass p-4">
                    <form action="{{ route('login') }}" method="POST" id="formLogin">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label form-label-custom">
                                <i class="bi bi-envelope me-1"></i> Email
                            </label>
                            <input type="email"
                                   class="form-control form-control-custom"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Masukan email"
                                   required
                                   autofocus>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label form-label-custom">
                                <i class="bi bi-key me-1"></i> Password
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control form-control-custom"
                                       id="password"
                                       name="password"
                                       placeholder="Masukan password"
                                       required>
                                <button type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-secondary pe-3"
                                        onclick="togglePassword()"
                                        style="text-decoration: none; z-index: 5;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Remember Me --}}
                        <div class="mb-4 form-check form-check-custom">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-gradient w-100 py-3" id="btnLogin">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Masuk
                        </button>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="text-center mt-3">
                    <small class="text-secondary">
                        <i class="bi bi-info-circle me-1"></i>
                        Hubungi administrator jika Anda lupa password
                    </small>
                </div>
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
