@extends('layouts.app')

@section('title', 'Verifikasi Email Anda')

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
        max-width: 450px;
        padding: 36px 28px;
        border-radius: 20px;
        position: relative;
        z-index: 1;
        text-align: center;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .auth-top-actions {
        position: absolute;
        top: 20px;
        right: 20px;
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
        margin-top: 12px;
        margin-bottom: 8px;
        font-size: 1.45rem;
    }

    .auth-title p {
        color: var(--auth-text-muted) !important;
        font-size: 0.88rem;
        line-height: 1.5;
        margin-bottom: 24px;
    }

    .btn-verify {
        background: #059669;
        border: none;
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
        width: 100%;
        padding: 13px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        margin-bottom: 12px;
    }

    .btn-verify:hover {
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
        <div class="auth-title">
            <div style="margin: 0 auto 12px; display: inline-block;">
                <div style="background: rgba(245, 158, 11, 0.15); width: 68px; height: 68px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-envelope-open-text" style="font-size: 28px; color: #D97706;"></i>
                </div>
            </div>
            <h2>Cek Email Anda</h2>
            <p>Terima kasih telah mendaftar! Kami telah mengirimkan tautan aktivasi ke email Anda. Silakan klik tautan tersebut untuk mulai menggunakan portal ini.</p>
        </div>

        @if (session('message'))
            <div style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 12px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.85rem; font-weight: 600;">
                <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i> Tautan verifikasi baru telah dikirimkan ke email Anda.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-verify">
                <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Kirim Ulang Email Aktivasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 10px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: var(--auth-text-muted); text-decoration: underline; cursor: pointer; font-size: 0.85rem;">
                Kembali & Keluar
            </button>
        </form>
    </div>
</div>

<script>
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