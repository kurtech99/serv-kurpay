@extends('layouts.app')

@section('title', 'Login Super Admin')

@section('content')
<style>
    :root {
        --auth-bg: #F8FAFC;
        --auth-card: #FFFFFF;
        --auth-border: #E2E8F0;
        --auth-text-main: #0F172A;
        --auth-text-muted: #64748B;
        --auth-input-bg: #F1F5F9;
        --auth-input-border: #CBD5E1;
        --auth-input-color: #0F172A;
        --auth-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.07);
        --auth-glow: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(248, 250, 252, 0) 70%);
    }

    [data-theme="dark"] {
        --auth-bg: #090D16;
        --auth-card: #111827;
        --auth-border: #1F2937;
        --auth-text-main: #F9FAFB;
        --auth-text-muted: #9CA3AF;
        --auth-input-bg: #0B0F19;
        --auth-input-border: rgba(255, 255, 255, 0.1);
        --auth-input-color: #FFFFFF;
        --auth-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        --auth-glow: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(9, 13, 22, 0) 70%);
    }

    body, html {
        height: 100%;
        margin: 0;
        background-color: var(--auth-bg) !important;
        color: var(--auth-text-main) !important;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .auth-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        width: 100%;
        padding: 40px 16px;
        position: relative;
        overflow: hidden;
    }

    .auth-wrapper::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: var(--auth-glow);
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 0;
        pointer-events: none;
    }

    .auth-card {
        background: var(--auth-card) !important;
        border: 1px solid var(--auth-border) !important;
        color: var(--auth-text-main) !important;
        box-shadow: var(--auth-shadow) !important;
        width: 100%;
        max-width: 420px;
        padding: clamp(28px, 6vw, 40px) clamp(20px, 5vw, 32px);
        border-radius: 20px;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .auth-top-actions {
        position: absolute;
        top: max(16px, env(safe-area-inset-top));
        right: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10;
    }

    .theme-auth-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--auth-card);
        color: var(--auth-text-main);
        border: 1px solid var(--auth-border);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .theme-auth-btn:hover {
        transform: rotate(15deg);
        border-color: #10B981;
    }

    .auth-title h2 {
        color: var(--auth-text-main) !important;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        letter-spacing: -0.5px;
        margin-top: 10px;
        margin-bottom: 4px;
        font-size: clamp(1.4rem, 3.5vw, 1.6rem);
    }

    .auth-title p {
        color: var(--auth-text-muted) !important;
        font-size: clamp(0.82rem, 2vw, 0.88rem);
    }

    .form-group label {
        color: var(--auth-text-main) !important;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        background: var(--auth-input-bg) !important;
        border: 1px solid var(--auth-input-border) !important;
        color: var(--auth-input-color) !important;
        font-size: 0.92rem;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .form-control::placeholder {
        color: var(--auth-text-muted) !important;
        opacity: 0.6;
    }

    .form-control:focus {
        background: var(--auth-card) !important;
        border-color: #10B981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
        outline: none;
    }

    .btn-login {
        background: #059669;
        border: none;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        cursor: pointer;
        width: 100%;
        padding: 13px;
        border-radius: 12px;
    }

    .btn-login:hover {
        background: #047857;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.45);
    }
</style>

<div class="auth-top-actions">
    <button class="theme-auth-btn" id="authThemeToggle" title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title" style="text-align: center; margin-bottom: 24px;">
            <div style="margin: 0 auto 12px; width: 76px; height: 76px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px rgba(16, 185, 129, 0.45); border: 2px solid #10b981; background: #000;">
                <img src="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}" alt="VPN KURTECH" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h2>KURTECH VPN</h2>
            <p>Portal Super Administrator</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; padding: 12px; border-radius: 12px; margin-bottom: 18px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.85rem; text-align:center; font-weight: 600;">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('/superadmin') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Email Admin</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--auth-text-muted);"></i>
                    <input type="email" name="email" class="form-control" placeholder="admin@kurtech" style="padding: 12px 16px 12px 44px; border-radius: 12px; width:100%; box-sizing: border-box;" required value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label>Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--auth-text-muted);"></i>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" style="padding: 12px 16px 12px 44px; border-radius: 12px; width:100%; box-sizing: border-box;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login" style="margin-bottom: 18px;">
                <i class="fa-solid fa-shield-halved" style="margin-right: 8px;"></i> MASUK SUPER ADMIN
            </button>

            <div style="text-align: center; padding-top: 10px; border-top: 1px solid var(--auth-border);">
                <a href="{{ url('/') }}" style="color: var(--auth-text-muted); text-decoration: none; font-size: 0.82rem; font-weight: 600;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Anti-BFCache: Paksa reload fresh session jika halaman dimuat dari memori/cache HP
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
            window.location.reload();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const html = document.documentElement;
        const toggle = document.getElementById('authThemeToggle');
        
        function applyAuthTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('vpn_theme', theme);
            localStorage.setItem('app-theme', theme);
            if (toggle) {
                toggle.innerHTML = theme === 'dark' ? '<i class="fa-solid fa-sun" style="color: #FBBF24;"></i>' : '<i class="fa-solid fa-moon"></i>';
            }
        }

        const saved = localStorage.getItem('vpn_theme') || localStorage.getItem('app-theme') || 'light';
        applyAuthTheme(saved);

        if (toggle) {
            toggle.addEventListener('click', () => {
                const current = html.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                applyAuthTheme(next);
            });
        }
    });
</script>
@endsection