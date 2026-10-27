@extends('layouts.app')

@section('title', 'Pendaftaran Tenant Baru')

@section('content')
<div style="max-width: 440px; margin: 30px auto 0;">
    <div class="kp-card" style="padding: 28px 24px;">
        <div style="text-align: center; margin-bottom: 22px;">
            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; margin: 0 auto 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-store"></i>
            </div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main); margin-bottom: 4px;">Daftar Tenant Baru</h2>
            <p style="font-size: 13px; color: var(--kp-text-muted);">Aktifkan Gateway QRIS untuk Mikhmon & Hotspot Anda</p>
        </div>

        <form action="{{ route('register') }}" method="POST">
            @csrf
            
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Nama Usaha / RT-RW Net</label>
                <input type="text" name="business_name" value="{{ old('business_name') }}" required placeholder="Contoh: Berkah WiFi Network" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Nomor WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Kata Sandi (Minimal 6 Karakter)</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 5px;">Ulangi Kata Sandi</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <button type="submit" class="kp-btn kp-btn-success" style="width: 100%; padding: 12px; font-size: 14px;">
                <i class="fa-solid fa-check"></i> Daftar & Aktifkan Gateway
            </button>
        </form>

        <div style="text-align: center; margin-top: 18px; font-size: 13px; color: var(--kp-text-muted);">
            Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--kp-primary); font-weight: 700; text-decoration: none;">Masuk Disini</a>
        </div>
    </div>
</div>
@endsection
