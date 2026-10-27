@extends('layouts.app')

@section('title', 'Buat Kata Sandi Baru')

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
        max-width: 440px;
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
        margin-bottom: 4px;
        font-size: 1.45rem;
    }

    .auth-title p {
        color: var(--auth-text-muted) !important;
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 18px;
        text-align: left;
    }

    .form-group label {
        color: var(--auth-text-main) !important;
        font-weight: 600;
        font-size: 0.85rem;
        display: block;
        margin-bottom: 8px;
    }

    .form-control {
        background: var(--auth-input-bg) !important;
        border: 1px solid var(--auth-input-border) !important;
        color: var(--auth-input-color) !important;
        width: 100%;
        padding: 12px 16px 12px 44px;
        border-radius: 12px;
        box-sizing: border-box;
        font-size: 0.92rem;
        transition: all 0.2s ease;
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

    .input-icon-wrapper {
        position: relative;
    }

    .input-icon-wrapper i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--auth-text-muted);
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
        margin-top: 10px;
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
        <div class="auth-title" style="margin-bottom: 24px;">
            <div style="margin: 0 auto 12px; display: inline-block;">
                <div style="background: rgba(16, 185, 129, 0.15); width: 68px; height: 68px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-key" style="font-size: 28px; color: #059669;"></i>
                </div>
            </div>
            <h2>Buat Kata Sandi Baru</h2>
            <p>Silakan masukkan kata sandi baru Anda di bawah ini.</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; padding: 12px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.85rem;">
                <ul style="margin: 0; padding-left: 20px; text-align: left;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" autocomplete="off">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ request()->email }}">

            <div class="form-group">
                <label>Kata Sandi Baru</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label>Ulangi Kata Sandi Baru</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang sandi baru" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn-verify">
                Simpan Kata Sandi Baru <i class="fa-solid fa-floppy-disk" style="margin-left: 8px;"></i>
            </button>
        </form>

        <div style="margin-top: 22px; padding-top: 14px; border-top: 1px solid var(--auth-border);">
            <a href="{{ route('login') }}" style="color: var(--auth-text-muted); font-size: 0.88rem; text-decoration: none;">
                Kembali ke halaman <strong style="color: #059669;">Masuk</strong>
            </a>
        </div>
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