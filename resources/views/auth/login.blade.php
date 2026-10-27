@extends('layouts.app')

@section('title', 'Masuk ke Kurtech Pay')

@section('content')
<div style="max-width: 420px; margin: 40px auto 0;">
    <div class="kp-card" style="padding: 28px 24px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="{{ asset('images/logo.jpg') }}" alt="KurPay" style="width: 72px; height: 72px; border-radius: 18px; object-fit: contain; margin: 0 auto 12px; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15);">
            <h2 style="font-size: 22px; font-weight: 800; color: var(--kp-text-main); margin-bottom: 4px;">KurPay</h2>
            <p style="font-size: 13px; color: var(--kp-text-muted);">Digital Payment Gateway Hotspot &amp; Billing</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 6px;">Username atau Email</label>
                <input type="text" name="login" value="{{ old('login') }}" required placeholder="Masukkan username atau email" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-font); outline: none; transition: border-color 0.2s;"
                       onfocus="this.style.borderColor='var(--kp-primary)'" onblur="this.style.borderColor='var(--kp-border)'">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 6px;">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Masukkan kata sandi" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-font); outline: none; transition: border-color 0.2s;"
                       onfocus="this.style.borderColor='var(--kp-primary)'" onblur="this.style.borderColor='var(--kp-border)'">
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--kp-text-muted); cursor: pointer;">
                    <input type="checkbox" name="remember" style="accent-color: var(--kp-primary);"> Ingat Saya
                </label>
            </div>

            <button type="submit" class="kp-btn kp-btn-primary" style="width: 100%; padding: 12px; font-size: 14.5px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--kp-text-muted);">
            Belum punya akun gateway? <a href="{{ route('register') }}" style="color: var(--kp-primary); font-weight: 700; text-decoration: none;">Daftar Tenant</a>
        </div>
    </div>
</div>
@endsection
