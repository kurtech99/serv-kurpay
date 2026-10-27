<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Dashboard') - Kurtech Pay Gateway</title>
    <meta name="description" content="Kurtech Pay - Payment Gateway Mandiri untuk Mikhmon Online & Billing WiFi RT-RW Net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --kp-primary: #0284c7;
            --kp-primary-dark: #0369a1;
            --kp-primary-light: #e0f2fe;
            --kp-success: #10b981;
            --kp-success-dark: #059669;
            --kp-success-light: #ecfdf5;
            --kp-warning: #f59e0b;
            --kp-danger: #ef4444;
            --kp-bg: #f8fafc;
            --kp-card: #ffffff;
            --kp-text-main: #0f172a;
            --kp-text-muted: #64748b;
            --kp-border: #e2e8f0;
            --kp-font: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --kp-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--kp-font);
            background: var(--kp-bg);
            color: var(--kp-text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-tap-highlight-color: transparent;
        }

        /* Navbar */
        .kp-navbar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--kp-border);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 12px 20px;
        }

        .kp-nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .kp-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--kp-text-main);
            font-weight: 800;
            font-size: 18px;
        }

        .kp-brand-logo {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
        }

        .kp-nav-menu {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kp-nav-link {
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--kp-text-muted);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kp-nav-link:hover, .kp-nav-link.active {
            background: var(--kp-primary-light);
            color: var(--kp-primary);
        }

        .kp-badge-balance {
            background: var(--kp-success-light);
            border: 1px solid #a7f3d0;
            color: var(--kp-success-dark);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            font-family: var(--kp-mono);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Main Content */
        .kp-main {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 24px 20px 80px;
        }

        /* Common Card */
        .kp-card {
            background: var(--kp-card);
            border: 1.5px solid var(--kp-border);
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        }

        .kp-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--kp-border);
        }

        .kp-card-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--kp-text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Buttons */
        .kp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .kp-btn-primary {
            background: linear-gradient(135deg, var(--kp-primary) 0%, var(--kp-primary-dark) 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        .kp-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
        }

        .kp-btn-success {
            background: linear-gradient(135deg, var(--kp-success) 0%, var(--kp-success-dark) 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .kp-btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        .kp-btn-outline {
            background: #ffffff;
            border: 1.5px solid var(--kp-border);
            color: var(--kp-text-muted);
        }

        .kp-btn-outline:hover {
            background: var(--kp-bg);
            border-color: #cbd5e1;
            color: var(--kp-text-main);
        }

        /* Alerts */
        .kp-alert {
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kp-alert-success {
            background: var(--kp-success-light);
            border: 1.5px solid #a7f3d0;
            color: #065f46;
        }

        .kp-alert-error {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            color: #991b1b;
        }

        /* Mobile Bottom Nav */
        @media (max-width: 768px) {
            .kp-nav-menu-desktop {
                display: none;
            }
            .kp-mobile-nav {
                display: flex !important;
            }
            .kp-main {
                padding: 16px 14px 90px;
            }
        }

        .kp-mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--kp-border);
            z-index: 99;
            padding: 8px 10px;
            justify-content: space-around;
        }

        .kp-mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--kp-text-muted);
            padding: 4px 8px;
            border-radius: 10px;
        }

        .kp-mobile-nav-item.active {
            color: var(--kp-primary);
        }

        .kp-mobile-nav-item i {
            font-size: 18px;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header Navbar -->
    <header class="kp-navbar">
        <div class="kp-nav-container">
            <a href="{{ auth()->check() && auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('merchant.dashboard') }}" class="kp-brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="KurPay" style="width: 36px; height: 36px; border-radius: 9px; object-fit: contain; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div>
                    <div style="font-size: 17px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">KurPay</div>
                    <div style="font-size: 10px; color: var(--kp-text-muted); font-weight: 700; margin-top: -3px; text-transform: uppercase; letter-spacing: 0.5px;">Payment Gateway</div>
                </div>
            </a>

            @auth
                <!-- Desktop Nav -->
                <nav class="kp-nav-menu kp-nav-menu-desktop">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="kp-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i> Master Panel
                        </a>
                        <a href="{{ route('admin.withdrawals') }}" class="kp-nav-link {{ request()->routeIs('admin.withdrawals') ? 'active' : '' }}">
                            <i class="fa-solid fa-money-bill-transfer"></i> Tarik Dana
                            @php $pendingCount = \App\Models\Withdrawal::where('status', 'pending')->count(); @endphp
                            @if($pendingCount > 0)
                                <span style="background: var(--kp-danger); color: #fff; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 999px;">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.merchants') }}" class="kp-nav-link {{ request()->routeIs('admin.merchants') ? 'active' : '' }}">
                            <i class="fa-solid fa-store"></i> Tenant
                        </a>
                        <a href="{{ route('admin.settings') }}" class="kp-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <i class="fa-solid fa-sliders"></i> Pengaturan
                        </a>
                    @else
                        <a href="{{ route('merchant.dashboard') }}" class="kp-nav-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i> Dashboard
                        </a>
                        <a href="{{ route('merchant.withdrawal') }}" class="kp-nav-link {{ request()->routeIs('merchant.withdrawal') ? 'active' : '' }}">
                            <i class="fa-solid fa-wallet"></i> Tarik Saldo
                        </a>
                        <a href="{{ route('merchant.api.credentials') }}" class="kp-nav-link {{ request()->routeIs('merchant.api.credentials') ? 'active' : '' }}">
                            <i class="fa-solid fa-code"></i> Integrasi API
                        </a>
                    @endif

                    <div style="height: 24px; width: 1px; background: var(--kp-border); margin: 0 4px;"></div>

                    @if(!auth()->user()->isSuperAdmin() && auth()->user()->merchant)
                        <div class="kp-badge-balance" title="Saldo Tersedia">
                            <i class="fa-solid fa-wallet" style="color: var(--kp-success);"></i>
                            Rp {{ number_format(auth()->user()->merchant->balance, 0, ',', '.') }}
                        </div>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="kp-btn kp-btn-outline" style="padding: 6px 12px; font-size: 12px;" title="Keluar">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>

    <!-- Main Body -->
    <main class="kp-main">
        @if(session('success'))
            <div class="kp-alert kp-alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="kp-alert kp-alert-error">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 16px;"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation -->
    @auth
        <nav class="kp-mobile-nav">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.dashboard') }}" class="kp-mobile-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Master</span>
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="kp-mobile-nav-item {{ request()->routeIs('admin.withdrawals') ? 'active' : '' }}">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>Pencairan</span>
                </a>
                <a href="{{ route('admin.merchants') }}" class="kp-mobile-nav-item {{ request()->routeIs('admin.merchants') ? 'active' : '' }}">
                    <i class="fa-solid fa-store"></i>
                    <span>Tenant</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="kp-mobile-nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Setting</span>
                </a>
            @else
                <a href="{{ route('merchant.dashboard') }}" class="kp-mobile-nav-item {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('merchant.withdrawal') }}" class="kp-mobile-nav-item {{ request()->routeIs('merchant.withdrawal') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Tarik Saldo</span>
                </a>
                <a href="{{ route('merchant.api.credentials') }}" class="kp-mobile-nav-item {{ request()->routeIs('merchant.api.credentials') ? 'active' : '' }}">
                    <i class="fa-solid fa-code"></i>
                    <span>API Key</span>
                </a>
            @endif
            <a href="#" onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();" class="kp-mobile-nav-item">
                <i class="fa-solid fa-arrow-right-from-bracket" style="color: var(--kp-danger);"></i>
                <span style="color: var(--kp-danger);">Keluar</span>
            </a>
            <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </nav>
    @endauth

    @yield('scripts')
</body>
</html>
