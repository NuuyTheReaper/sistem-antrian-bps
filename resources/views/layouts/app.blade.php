<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Antrian Pelayanan Publik')</title>
    <meta name="description" content="Sistem Antrian Pelayanan Publik Berbasis Website - Layanan cepat, transparan, dan modern.">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Bootstrap Icons (Lokal untuk akses tanpa internet) --}}
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;       /* Bright Blue */
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --secondary: #0ea5e9;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #f8fafc;          /* Light Off-White Background */
            --dark-card: #ffffff;      /* Pure White Card */
            --dark-surface: #f1f5f9;   /* Very Light Gray */
            --text-primary: #1e293b;   /* Dark Slate Text */
            --text-secondary: #64748b; /* Medium Slate Text */
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.08); /* Dark subtle border */
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            --gradient-warm: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #0ea5e9 100%);
            --shadow-glow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Soft drop shadow */
            --radius: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── Animated Background ──────────────────────────── */
        .bg-animated {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        .bg-animated::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(79,70,229,0.15) 0%, transparent 70%);
            top: -100px; right: -100px;
            animation: float 8s ease-in-out infinite;
        }
        .bg-animated::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.1) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            animation: float 10s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* ─── Navbar ───────────────────────────────────────── */
        .navbar-custom {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
        }
        .navbar-brand-custom {
            font-weight: 800;
            font-size: 1.25rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        .navbar-brand-custom i {
            -webkit-text-fill-color: initial;
            color: var(--primary-light);
            margin-right: 8px;
        }

        /* ─── Cards ────────────────────────────────────────── */
        .card-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-glow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 60px rgba(79, 70, 229, 0.2);
        }

        /* ─── Buttons ──────────────────────────────────────── */
        .btn-gradient {
            background: var(--gradient-primary);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
            color: white;
        }
        .btn-gradient:hover::before {
            left: 100%;
        }

        /* Override outline-light untk tema cerah */
        .btn-outline-light {
            color: var(--primary) !important;
            border-color: var(--glass-border) !important;
            background: var(--dark-card) !important;
            font-weight: 600;
        }
        .btn-outline-light:hover {
            background: var(--primary-light) !important;
            color: #ffffff !important;
            border-color: var(--primary-light) !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }

        .btn-success-custom {
            background: var(--gradient-success);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
        }
        .btn-success-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-warning-custom {
            background: var(--gradient-warm);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
        }
        .btn-warning-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .btn-danger-custom {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
        }
        .btn-danger-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
            color: white;
        }

        /* ─── Forms ────────────────────────────────────────── */
        .form-control-custom,
        .form-select-custom {
            background: var(--dark-card);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus,
        .form-select-custom:focus {
            background: var(--dark-surface);
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            color: var(--text-primary);
        }
        .form-control-custom::placeholder {
            color: var(--text-secondary);
        }
        .form-label-custom {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        /* ─── Stat Cards ───────────────────────────────────── */
        .stat-card {
            padding: 1.5rem;
            border-radius: var(--radius);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
        }
        .stat-card .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            font-weight: 600;
        }

        /* ─── Status Badges ────────────────────────────────── */
        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-menunggu {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .badge-dipanggil {
            background: rgba(79, 70, 229, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(79, 70, 229, 0.3);
            animation: pulse-badge 2s ease-in-out infinite;
        }
        .badge-selesai {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .badge-dilewati {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* ─── Table ────────────────────────────────────────── */
        .table-custom {
            color: var(--text-primary);
        }
        .table-custom thead th {
            background: var(--dark-card);
            border-bottom: 2px solid var(--glass-border);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 16px;
        }
        .table-custom tbody td {
            border-bottom: 1px solid var(--glass-border);
            padding: 14px 16px;
            vertical-align: middle;
        }
        .table-custom tbody tr {
            transition: background 0.2s ease;
        }
        .table-custom tbody tr:hover {
            background: rgba(79, 70, 229, 0.05);
        }

        /* ─── Alert Custom ─────────────────────────────────── */
        .alert-custom {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            border-left: 4px solid var(--success);
        }

        /* ─── Pulse Animation ──────────────────────────────── */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 1; }
            50% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(0.95); opacity: 1; }
        }
        .pulse-active {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        /* ─── Responsive ───────────────────────────────────── */
        @media (max-width: 768px) {
            .stat-card .stat-number {
                font-size: 1.8rem;
            }
        }

        /* ─── Page Content ─────────────────────────────────── */
        .page-content {
            min-height: calc(100vh - 80px);
            padding: 2rem 0;
        }

        /* ─── Footer ───────────────────────────────────────── */
        .footer-custom {
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            padding: 1rem 0;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* ─── Select option fix ────────────────────────────── */
        .form-select-custom option {
            background: var(--dark-card);
            color: var(--text-primary);
        }

        /* ─── Navbar Buttons Fix ───────────────────────────── */
        .navbar .btn {
            padding: 0.35rem 1.25rem !important;
            height: 36px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem !important;
        }
        @media (max-width: 768px) {
            .navbar .btn {
                padding: 0.25rem 0.75rem !important;
                height: 32px !important;
                font-size: 0.8rem !important;
            }
        /* ─── Watermark Background ─────────────────────────── */
        .bg-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80vw;
            height: 80vw;
            max-width: 600px;
            max-height: 600px;
            background-image: url('{{ asset("images/logo-bps.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.04; /* Sangat tipis agar tidak mengganggu fokus (4%) */
            z-index: -1;
            pointer-events: none;
        }

    </style>

    @stack('styles')
</head>
<body>

    {{-- Background Watermark --}}
    <div class="bg-animated"></div>
    <div class="bg-watermark"></div>

    {{-- Navbar --}}
    <nav class="navbar navbar-custom sticky-top">
        <div class="container flex-nowrap">
            <a class="navbar-brand navbar-brand-custom text-truncate me-2" href="/">
                <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS" style="height: 28px; width: auto; margin-right: 8px;">
                <span class="d-none d-md-inline">Antrian Pelayanan Publik BPS Tegal</span>
                <span class="d-inline d-md-none">BPS Tegal</span>
            </a>
            <div class="d-flex gap-1 gap-md-2 align-items-center flex-shrink-0">
                <a href="{{ route('antrian.daftar') }}" class="btn btn-sm btn-outline-light rounded-pill px-2 px-md-3">
                    <i class="bi bi-person-plus me-1"></i> Daftar
                </a>
                @auth
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-2 px-md-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="background: var(--dark-card); border-color: var(--glass-border);">
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item" style="color: var(--text-primary);">
                                    <i class="bi bi-speedometer2 me-2" style="color: var(--primary-light);"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporan') }}" class="dropdown-item" style="color: var(--text-primary);">
                                    <i class="bi bi-graph-up-arrow me-2" style="color: var(--secondary);"></i> Laporan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger" style="color: #fca5a5 !important;">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-gradient rounded-pill px-2 px-md-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> <span class="d-none d-sm-inline">Login Admin</span><span class="d-inline d-sm-none">Login</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="container mt-3">
        @if(session('sukses'))
            <div class="alert alert-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-custom alert-dismissible fade show" role="alert" style="border-left-color: var(--accent);">
                <i class="bi bi-info-circle-fill text-warning me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="page-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer-custom">
        <div class="container">
            &copy; {{ date('Y') }} Sistem Antrian Pelayanan Publik &mdash; Dibangun dengan <i class="bi bi-heart-fill text-danger"></i> Laravel
        </div>
    </footer>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
