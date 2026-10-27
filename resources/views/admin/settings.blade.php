@extends('layouts.app')

@section('title', 'Pengaturan Gateway Kurtech Pay')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main);">Pengaturan Gateway (Super Admin)</h2>
    <p style="font-size: 13px; color: var(--kp-text-muted);">Konfigurasi tarif penarikan saldo, jam operasional, limit transaksi, dan webhook DANA.</p>
</div>

<form action="{{ route('admin.settings.save') }}" method="POST">
    @csrf

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">

        <!-- 1. PENGATURAN PENARIKAN (TARIK SALDO) -->
        <div class="kp-card" style="margin-bottom: 0;">
            <div class="kp-card-header">
                <div class="kp-card-title">
                    <i class="fa-solid fa-coins" style="color: #f59e0b;"></i> Aturan Penarikan Saldo Tenant
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Biaya Admin Transaksi Pelanggan (%)
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="number" step="0.01" name="customer_fee_percent" value="{{ old('customer_fee_percent', $settings['customer_fee_percent']) }}" required 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 800; color: #2563eb; outline: none;">
                    <span style="font-weight: 800; color: var(--kp-text-muted);">%</span>
                </div>
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">
                    Biaya tambahan untuk pembeli voucher / tagihan wifi rumahan (Contoh: 0.3%).
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Biaya Admin Penarikan Tenant (Flat)
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; color: var(--kp-text-muted);">Rp</span>
                    <input type="number" name="withdraw_fee" value="{{ old('withdraw_fee', $settings['withdraw_fee']) }}" required 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 800; color: #dc2626; outline: none;">
                </div>
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">
                    Biaya yang dipotong dari saldo tenant setiap kali melakukan penarikan dana.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                        Minimal Penarikan (Rp)
                    </label>
                    <input type="number" name="withdraw_min" value="{{ old('withdraw_min', $settings['withdraw_min']) }}" required 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                        Maksimal Penarikan (Rp)
                    </label>
                    <input type="number" name="withdraw_max" value="{{ old('withdraw_max', $settings['withdraw_max']) }}" required 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                        Jam Buka Penarikan
                    </label>
                    <input type="text" name="withdraw_open_time" value="{{ old('withdraw_open_time', $settings['withdraw_open_time']) }}" required placeholder="08:00" 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                        Jam Tutup Penarikan
                    </label>
                    <input type="text" name="withdraw_close_time" value="{{ old('withdraw_close_time', $settings['withdraw_close_time']) }}" required placeholder="14:00" 
                           style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
                </div>
            </div>
            <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 6px;">
                Format jam 24 jam (Contoh: 08:00 dan 14:00).
            </div>
        </div>

        <!-- 2. QRIS & DANA WEBHOOK -->
        <div class="kp-card" style="margin-bottom: 0;">
            <div class="kp-card-header">
                <div class="kp-card-title">
                    <i class="fa-solid fa-qrcode" style="color: var(--kp-primary);"></i> QRIS DANA &amp; Webhook
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Nama Merchant QRIS
                </label>
                <input type="text" name="merchant_qris_name" value="{{ old('merchant_qris_name', $settings['merchant_qris_name']) }}" required 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-weight: 700; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    NMID QRIS
                </label>
                <input type="text" name="merchant_nmid" value="{{ old('merchant_nmid', $settings['merchant_nmid']) }}" required 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    String QRIS DANA Kurtech (EMVCo Payload)
                </label>
                <textarea name="qris_kurtech_payload" rows="3" required 
                          style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 11px; font-family: var(--kp-mono); outline: none;">{{ old('qris_kurtech_payload', $settings['qris_kurtech_payload']) }}</textarea>
                <div style="font-size: 11px; color: #059669; margin-top: 4px;">
                    <i class="fa-solid fa-bolt"></i> String QRIS statis ini digunakan untuk mengubah QRIS menjadi <b>QRIS Dinamis Otomatis Nominal</b> saat checkout.
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Secret Key Notifikasi DANA
                </label>
                <input type="text" name="dana_secret" value="{{ old('dana_secret', $settings['dana_secret']) }}" required 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); outline: none;">
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">
                    Endpoint DANA Webhook: <code>{{ url('/api/v1/callback/dana') }}</code>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Nomor WhatsApp Admin Kurtech
                </label>
                <input type="text" name="admin_whatsapp" value="{{ old('admin_whatsapp', $settings['admin_whatsapp']) }}" placeholder="6283826164767" 
                       style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); outline: none;">
            </div>
        </div>

    </div>

    <div style="text-align: right;">
        <button type="submit" class="kp-btn kp-btn-primary" style="padding: 12px 28px; font-size: 14px;">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Pengaturan
        </button>
    </div>
</form>
@endsection
