<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Antrian BPS Tegal')</title>
    
    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Google Fonts - Outfit for modern app look --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --app-bg: #f8f9fc;
            --app-surface: #ffffff;
            --primary: #4F46E5;
            --primary-light: #818CF8;
            --primary-dark: #3730A3;
            --secondary: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --radius-xl: 24px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 8px 24px rgba(149, 157, 165, 0.1);
            --shadow-floating: 0 10px 40px rgba(79, 70, 229, 0.15);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--app-bg);
            color: var(--text-main);
            -webkit-tap-highlight-color: transparent;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            position: relative;
        }

        main {
            flex: 1 0 auto;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #f8f9fc;
            z-index: -2;
            pointer-events: none;
        }

        /* Animated Background Blobs */
        .blob-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(129, 140, 248, 0.05) 100%);
            filter: blur(80px);
            border-radius: 50%;
            animation: blob-float 20s infinite alternate;
        }
        .blob-1 { top: -100px; right: -100px; animation-delay: 0s; }
        .blob-2 { bottom: -100px; left: -100px; background: rgba(16, 185, 129, 0.05); animation-delay: -5s; }
        .blob-3 { top: 40%; left: 20%; width: 300px; height: 300px; background: rgba(245, 158, 11, 0.05); animation-delay: -10s; }

        @keyframes blob-float {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        body::after {
            content: '';
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 400px; height: 400px;
            background-image: url('{{ asset('images/logo-bps.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.02;
            z-index: -1;
            pointer-events: none;
        }

        /* App Bar */
        .app-bar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1030;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            padding: 12px 0;
        }
        
        .app-title {
            font-weight: 800;
            font-size: 1.15rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .app-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modern Cards */
        .card-app {
            background: var(--app-surface);
            border-radius: var(--radius-xl);
            border: none;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 16px;
            animation: app-fade-in-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes app-fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Staggered load effects */
        .row > div:nth-child(1) .card-app { animation-delay: 0.1s; }
        .row > div:nth-child(2) .card-app { animation-delay: 0.2s; }
        .row > div:nth-child(3) .card-app { animation-delay: 0.3s; }
        .row > div:nth-child(4) .card-app { animation-delay: 0.4s; }
        
        .card-glass {
            background: var(--app-surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        /* Form Inputs */
        .form-control, .form-select {
            background-color: #F1F5F9;
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            background-color: #FFFFFF;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .form-control::placeholder {
            color: #94A3B8;
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        /* ─── Responsive Tables ───────────────────────────── */
        .table-app {
            color: var(--text-main);
            margin-bottom: 0;
        }
        .table-app th {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem;
        }
        .table-app td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        /* Premium Floating Table Rows */
        @media (min-width: 768px) {
            .table-floating {
                border-collapse: separate !important;
                border-spacing: 0 16px !important;
                background: transparent !important;
            }
            .table-floating thead th {
                border: none !important;
                background: transparent !important;
                font-weight: 800 !important;
                letter-spacing: 1px;
                padding: 0 1.5rem 0.5rem 1.5rem !important;
                color: var(--text-muted) !important;
                text-align: left;
            }
            .table-floating thead th.text-end {
                text-align: right !important;
            }
            .table-floating thead th.text-center {
                text-align: center !important;
            }
            .table-floating tbody tr {
                background: #ffffff;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
                border-radius: 24px;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .table-floating tbody tr:hover {
                transform: translateY(-5px) scale(1.01);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
                z-index: 10;
                position: relative;
            }
            .table-floating tbody td {
                border: none !important;
                padding: 1.5rem !important;
                vertical-align: middle;
                background: #ffffff;
            }
            .table-floating tbody td:first-child {
                border-top-left-radius: 24px;
                border-bottom-left-radius: 24px;
                border-left: 6px solid transparent;
            }
            .table-floating tbody td:last-child {
                border-top-right-radius: 24px;
                border-bottom-right-radius: 24px;
            }
            
            /* Status accent colors on the left border */
            .table-floating tbody tr[data-status="menunggu"] td:first-child { border-left-color: var(--primary); }
            .table-floating tbody tr[data-status="dipanggil"] td:first-child { border-left-color: var(--success); }
            .table-floating tbody tr[data-status="selesai"] td:first-child { border-left-color: var(--text-muted); }
            .table-floating tbody tr[data-status="dilewati"] td:first-child { border-left-color: #DB2777; }
        }


        /* Responsive Mobile Table to Cards */
        @media (max-width: 768px) {
            .table-mobile-cards, .table-mobile-cards thead, .table-mobile-cards tbody, .table-mobile-cards th, .table-mobile-cards td, .table-mobile-cards tr {
                display: block;
                width: 100% !important;
            }
            .table-mobile-cards thead {
                display: none !important;
            }
            .table-mobile-cards tr {
                margin-bottom: 1.25rem;
                padding: 1.25rem;
                border-radius: var(--radius-xl);
                background: #ffffff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
                border: 1px solid var(--border-color);
                position: relative;
                transition: all 0.3s ease;
            }
            .table-mobile-cards tr:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            }
            .table-mobile-cards td {
                padding: 12px 0;
                border-bottom: 1px dashed var(--border-color);
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: flex-start;
                text-align: left;
                font-size: 0.85rem;
                gap: 4px;
            }
            .table-mobile-cards td:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }
            .table-mobile-cards td::before {
                content: attr(data-label);
                display: block;
                font-weight: 800;
                color: var(--text-muted);
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }
            .table-mobile-cards .badge-kode-antrian {
                font-size: 0.95rem !important;
                padding: 6px 12px !important;
            }
            .table-mobile-cards td[data-label="Aksi"] {
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 8px;
                padding-top: 16px;
                flex-wrap: wrap;
            }
            .table-mobile-cards td[data-label="Aksi"]::before {
                display: none;
            }
        }

        /* ─── Buttons ─────────────────────────────────────── */
        .btn-app {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius-xl);
            padding: 16px 24px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: var(--shadow-floating);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-app:hover, .btn-app:active {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.25);
            color: white;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            transition: all 0.3s;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2);
            color: white;
        }

        .btn-gradient-danger {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            border: none;
            transition: all 0.3s;
        }
        .btn-gradient-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-danger-custom {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-danger-custom:hover {
            background: #DC2626;
            color: white;
        }

        .btn-success-custom {
            background: #D1FAE5;
            color: #059669;
            border: 1px solid #6EE7B7;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-success-custom:hover {
            background: #059669;
            color: white;
        }
        
        
        .btn-icon-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F1F5F9;
            color: var(--text-main);
            transition: all 0.2s;
            border: none;
            font-size: 1.1rem;
        }
        .btn-icon-circle:hover {
            background: #E2E8F0;
        }
        
        .btn-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 16px 6px 6px;
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }
        .btn-profile:hover {
            background: #F8FAFC;
            border-color: var(--primary-light);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }
        .btn-profile .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .btn-profile .name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .btn-profile i {
            font-size: 0.8rem;
            color: var(--text-muted);
            transition: transform 0.3s ease;
        }
        .dropdown.show .btn-profile i {
            transform: rotate(180deg);
        }

        /* Status badges */
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-menunggu { background: #FEF3C7; color: #D97706; }
        .badge-dipanggil { background: #E0E7FF; color: #4338CA; animation: pulse-ring 2s infinite; }
        .badge-selesai { background: #D1FAE5; color: #059669; }
        .badge-dilewati { background: #FEE2E2; color: #DC2626; }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }

        /* Glass dropdown */
        .dropdown-menu-app {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 8px;
            margin-top: 10px;
            min-width: 200px;
        }
        .dropdown-menu-app .dropdown-item {
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
        }
        .dropdown-menu-app .dropdown-item:hover {
            background-color: #F1F5F9;
        }
        .dropdown-menu-app .dropdown-item i {
            font-size: 1.1rem;
        }

        .alert-app {
            border-radius: var(--radius-md);
            border: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .app-title { font-size: 0.95rem; }
            .app-subtitle { font-size: 0.6rem; }
            .btn-profile { padding: 4px 10px 4px 4px; gap: 6px; }
            .btn-profile .avatar { width: 26px; height: 26px; font-size: 0.8rem; }
            .app-bar .container { padding-left: 10px; padding-right: 10px; }
            .btn-icon-circle { width: 36px; height: 36px; font-size: 1rem; }
            header .btn-outline-primary { padding: 6px 10px !important; font-size: 0.75rem !important; }
        }

        /* Global Footer Style */
        .app-footer {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            padding: 32px 0;
            margin-top: 48px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .app-footer .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .app-footer .footer-logo {
            height: 38px;
            object-fit: contain;
        }
        .app-footer .footer-title {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.3px;
        }
        .app-footer .footer-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="blob-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    {{-- App Bar --}}
    <header class="app-bar">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 1200px;">
            <a href="/" class="text-decoration-none d-flex align-items-center gap-3">
                <div style="height: 42px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS" style="height: 100%; object-fit: contain;">
                </div>
                <div>
                    <h1 class="app-title">BPS Tegal</h1>
                    <div class="app-subtitle">Sistem Antrian Cerdas</div>
                </div>
            </a>
            
            <div class="d-flex align-items-center gap-2">
                @auth
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 px-3 py-2 border" style="border-radius: 12px; font-weight: 600; font-size: 0.85rem;" onclick="showQrModal()">
                        <i class="bi bi-qr-code-scan"></i>
                        <span class="d-none d-sm-inline">QR Pendaftaran</span>
                    </button>
                    
                    <div class="dropdown">
                        <div class="btn-profile" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                            <div class="avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <span class="name d-none d-sm-block">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <i class="bi bi-chevron-down ms-1"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-app">
                            <li class="px-3 py-2">
                                <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Masuk Sebagai</div>
                                <div class="fw-bold">{{ Auth::user()->name }} <span class="badge bg-secondary text-white" style="font-size: 0.6rem; vertical-align: middle;">{{ ucfirst(Auth::user()->role) }}</span></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-fill me-2 text-primary"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.riwayat') }}"><i class="bi bi-clock-history me-2 text-warning"></i> Riwayat Antrian</a></li>
                            @if(Auth::user()->role === 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.laporan') }}"><i class="bi bi-pie-chart-fill me-2 text-success"></i> Laporan</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.petugas.index') }}"><i class="bi bi-people-fill me-2 text-info"></i> Kelola Petugas</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-icon-circle text-muted" title="Login Admin">
                        <i class="bi bi-shield-lock-fill"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    <div class="container mt-3" style="max-width: 600px;">
        @if(session('sukses'))
            <div class="alert alert-success alert-app alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('sukses') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-warning alert-app alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="py-3">
        @yield('content')
    </main>

    {{-- Global Footer --}}
    <footer class="app-footer mt-auto">
        <div class="container" style="max-width: 1200px;">
            <div class="row align-items-center justify-content-between g-4">
                <div class="col-12 col-md-6 d-flex flex-column align-items-center align-items-md-start">
                    <div class="footer-brand">
                        <img src="{{ asset('images/logo-uhn.png') }}" alt="Logo UHN" class="footer-logo">
                        <div>
                            <h5 class="footer-title">Universitas Harkat Negeri</h5>
                            <span class="footer-subtitle">Mahasiswa Pengembang Sistem</span>
                        </div>
                    </div>
                    <p class="mb-0 text-center text-md-start" style="font-size: 0.8rem; opacity: 0.85; max-width: 480px;">
                        Sistem Antrian Cerdas dikembangkan oleh Mahasiswa Universitas Harkat Negeri untuk modernisasi dan digitalisasi layanan publik pada Badan Pusat Statistik Kota Tegal.
                    </p>
                </div>
                <div class="col-12 col-md-6 d-flex flex-column align-items-center align-items-md-end gap-2">
                    <div class="d-flex gap-3 text-muted">
                        <a href="https://harkatnegeri.ac.id" target="_blank" class="text-decoration-none text-muted hover:text-primary transition" style="font-size: 0.8rem;">
                            <i class="bi bi-globe me-1"></i> harkatnegeri.ac.id
                        </a>
                        <span class="opacity-25">|</span>
                        <a href="#" class="text-decoration-none text-muted hover:text-primary transition" style="font-size: 0.8rem;">
                            <i class="bi bi-envelope me-1"></i> Hubungi IT Support
                        </a>
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.75;" class="text-center text-md-end">
                        &copy; {{ date('Y') }} Universitas Harkat Negeri & BPS Kota Tegal. Hak Cipta Dilindungi.
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                            <i class="bi bi-shield-fill-check me-1"></i> Sistem Terenkripsi & Aman
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                            <i class="bi bi-cloud-check-fill me-1"></i> Monitoring Online Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Modal QR Code Pendaftaran --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px;">
                    <h5 class="modal-title fw-bold" id="qrModalLabel">
                        <i class="bi bi-qr-code-scan text-primary me-2"></i> QR Code Pendaftaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="padding: 30px 24px;">
                    <p class="text-muted mb-4" style="font-size: 0.9rem;">
                        Scan QR Code di bawah menggunakan kamera smartphone pengunjung untuk langsung membuka form pendaftaran antrian BPS Tegal.
                    </p>
                    
                    {{-- QR Code Image Container --}}
                    <div class="d-inline-block p-3 bg-white border mb-3" style="border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <img id="qrCodeImg" src="" alt="QR Code Pendaftaran" style="width: 240px; height: 240px; display: block;">
                    </div>
                    
                    {{-- URL Link Display --}}
                    <div class="mb-4">
                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem; font-weight: 600;">LINK PENDAFTARAN:</small>
                        <code class="px-3 py-2 bg-light border text-primary fw-bold" style="border-radius: 10px; font-size: 0.85rem; word-break: break-all;" id="qrCodeLink"></code>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem;">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printQrCode()" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: var(--primary); border: none; padding: 8px 20px;">
                        <i class="bi bi-printer me-1"></i> Cetak QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showQrModal() {
            const link = window.location.origin + '/antrian/daftar';
            document.getElementById('qrCodeLink').textContent = link;
            document.getElementById('qrCodeImg').src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(link);
            
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();
        }

        function printQrCode() {
            const link = window.location.origin + '/antrian/daftar';
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' + encodeURIComponent(link);
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak QR Code Pendaftaran</title>
                    <style>
                        body {
                            font-family: 'Outfit', sans-serif;
                            text-align: center;
                            padding: 50px;
                            color: #0F172A;
                            background: #F8FAFC;
                        }
                        .container {
                            max-width: 440px;
                            margin: 0 auto;
                            border: 1px solid #E2E8F0;
                            border-radius: 28px;
                            padding: 40px;
                            background: #ffffff;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                        }
                        h1 {
                            color: #4F46E5;
                            margin-bottom: 5px;
                            font-size: 2rem;
                            font-weight: 900;
                            letter-spacing: -0.5px;
                        }
                        p {
                            font-size: 1rem;
                            color: #64748B;
                            margin-bottom: 30px;
                        }
                        img {
                            width: 260px;
                            height: 260px;
                            margin-bottom: 30px;
                        }
                        .url {
                            font-weight: bold;
                            color: #4F46E5;
                            font-size: 1rem;
                            padding: 12px 24px;
                            background: #F1F5F9;
                            border-radius: 12px;
                            display: inline-block;
                            word-break: break-all;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>BPS KOTA TEGAL</h1>
                        <p>Scan QR Code di bawah untuk Ambil Antrian Digital</p>
                        <img id="qr-image" src="${qrUrl}" alt="QR Code">
                        <br>
                        <div class="url">${link}</div>
                    </div>
                    <script>
                        // Wait for the QR image to load before opening print dialog
                        var img = document.getElementById('qr-image');
                        img.onload = function() {
                            setTimeout(function() {
                                window.print();
                            }, 250);
                        };
                        // Close window automatically after the print dialog is closed
                        window.onafterprint = function() {
                            window.close();
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
    @stack('scripts')
</body>
</html>
