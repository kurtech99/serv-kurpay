@extends('layouts.app')

@section('title', 'Dashboard Merchant - KurPay')

@section('content')
<!-- Header Banner -->
<div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 20px; padding: 26px 24px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.25); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
        <div style="font-size: 13px; font-weight: 700; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Selamat Datang,</div>
        <h1 style="font-size: 24px; font-weight: 800; margin-top: 2px;">{{ $merchant->business_name }}</h1>
        <div style="font-size: 12.5px; opacity: 0.85; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span>ID Merchant: <b>{{ $merchant->merchant_code }}</b></span>
            <span>•</span>
            <span>Status: <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 999px;">Aktif</span></span>
            <span>•</span>
            <span>Mode Gateway: 
                <b style="background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 999px;">
                    {{ $merchant->gateway_mode === 'custom' ? 'QRIS Sendiri' : 'Sewa Gateway Kurtech' }}
                </b>
            </span>
            <span>•</span>
            <a href="{{ route('merchant.merchants') }}" style="color: #ffffff; text-decoration: underline; font-weight: 700;">
                <i class="fa-solid fa-arrows-rotate"></i> Ganti Router ({{ auth()->user()->merchants()->count() }} Terdaftar)
            </a>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('merchant.merchants') }}" class="kp-btn" style="background: rgba(255,255,255,0.2); border: 1.5px solid rgba(255,255,255,0.4); color: #ffffff; padding: 11px 16px; font-size: 13.5px;">
            <i class="fa-solid fa-store"></i> Kelola Router
        </a>
        <a href="{{ route('merchant.withdrawal') }}" class="kp-btn" style="background: #ffffff; color: #0284c7; padding: 11px 18px; font-size: 13.5px; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
            <i class="fa-solid fa-money-bill-transfer"></i> Tarik Saldo Dompet
        </a>
        <a href="#section-topup-fee" class="kp-btn" style="background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.4); color: #ffffff; padding: 11px 18px; font-size: 13.5px;">
            <i class="fa-solid fa-coins"></i> Isi Deposit Fee
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Saldo Dompet -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-success);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Saldo Dompet KurPay</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--kp-success-light); color: var(--kp-success); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-success-dark); font-family: var(--kp-mono);">
            Rp {{ number_format($merchant->balance, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Hasil Mode Gateway Kurtech (Bisa ditarik)
        </div>
    </div>

    <!-- Saldo Kuota Deposit Fee (Khusus Mode QRIS Sendiri) -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Saldo Kuota Deposit Fee</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-piggy-bank"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #d97706; font-family: var(--kp-mono);">
            Rp {{ number_format($merchant->fee_deposit_balance, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Kuota fee 0.3% khusus Mode QRIS Sendiri
        </div>
    </div>

    <!-- Pendapatan Hari Ini -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-primary);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Penjualan Hari Ini</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--kp-primary-light); color: var(--kp-primary); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main); font-family: var(--kp-mono);">
            Rp {{ number_format($todayRevenue, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Total voucher laku hari ini
        </div>
    </div>

    <!-- Total Transaksi Sukses -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Voucher Terjual</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-ticket"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main);">
            {{ number_format($totalTransactions, 0, ',', '.') }} <span style="font-size: 14px; font-weight: 600; color: var(--kp-text-muted);">Pcs</span>
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Transaksi sukses keseluruhan
        </div>
    </div>
</div>

<!-- DUAL GATEWAY CONFIGURATION & TOP UP DEPOSIT SECTION -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
    
    <!-- CARD 1: PENGATURAN MODE GATEWAY -->
    <div class="kp-card" style="margin-bottom: 0;">
        <div class="kp-card-header">
            <div class="kp-card-title">
                <i class="fa-solid fa-sliders" style="color: var(--kp-primary);"></i> Mode Gateway Pembayaran
            </div>
            <span style="font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: {{ $merchant->gateway_mode === 'custom' ? '#dbeafe; color: #1e40af;' : '#dcfce7; color: #15803d;' }}">
                {{ $merchant->gateway_mode === 'custom' ? 'MODE: QRIS SENDIRI' : 'MODE: GATEWAY KURTECH' }}
            </span>
        </div>

        <form action="{{ route('merchant.gateway.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Mode Selection Options -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <!-- Option 1: Kurtech Gateway -->
                <label style="border: 2px solid {{ $merchant->gateway_mode !== 'custom' ? 'var(--kp-primary)' : 'var(--kp-border)' }}; border-radius: 14px; padding: 14px; cursor: pointer; display: block; background: {{ $merchant->gateway_mode !== 'custom' ? '#f0f9ff' : '#fff' }}; transition: all 0.2s;" onclick="toggleCustomFields(false)">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <input type="radio" name="gateway_mode" value="kurtech" {{ $merchant->gateway_mode !== 'custom' ? 'checked' : '' }} onchange="toggleCustomFields(false)">
                        <b style="font-size: 13.5px; color: var(--kp-text-main);">Gateway Kurtech</b>
                    </div>
                    <p style="font-size: 11.5px; color: var(--kp-text-muted); line-height: 1.4; margin: 0;">
                        Pakai QRIS DANA Kurtech. Saldo ditampung di dompet KurPay & bisa dicairkan kapan saja (Fee tarik Rp 10.000).
                    </p>
                </label>

                <!-- Option 2: Custom QRIS -->
                <label style="border: 2px solid {{ $merchant->gateway_mode === 'custom' ? 'var(--kp-primary)' : 'var(--kp-border)' }}; border-radius: 14px; padding: 14px; cursor: pointer; display: block; background: {{ $merchant->gateway_mode === 'custom' ? '#f0f9ff' : '#fff' }}; transition: all 0.2s;" onclick="toggleCustomFields(true)">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <input type="radio" name="gateway_mode" value="custom" {{ $merchant->gateway_mode === 'custom' ? 'checked' : '' }} onchange="toggleCustomFields(true)">
                        <b style="font-size: 13.5px; color: var(--kp-text-main);">QRIS Sendiri</b>
                    </div>
                    <p style="font-size: 11.5px; color: var(--kp-text-muted); line-height: 1.4; margin: 0;">
                        Uang pembeli 100% langsung masuk ke DANA Anda! Fee 0.3% dipotong dari Saldo Deposit Kuota Fee.
                    </p>
                </label>
            </div>

            <!-- Custom QRIS Details (Shown when 'custom' selected) -->
            <div id="customQrisContainer" style="display: {{ $merchant->gateway_mode === 'custom' ? 'block' : 'none' }}; background: #f8fafc; border: 1.5px solid var(--kp-border); border-radius: 14px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 12.5px; font-weight: 800; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-qrcode" style="color: var(--kp-primary);"></i> Informasi QRIS DANA Bisnis Anda
                </div>

                <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 14px;">
                    @if($merchant->custom_qris_image)
                        <div style="text-align: center;">
                            <img src="{{ asset('storage/' . $merchant->custom_qris_image) }}" style="width: 80px; height: 80px; object-fit: contain; border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; padding: 4px;">
                            <div style="font-size: 10px; color: var(--kp-text-muted); margin-top: 3px;">QRIS Saat Ini</div>
                        </div>
                    @endif
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 4px;">Upload Gambar QRIS Anda (JPG/PNG)</label>
                        <input type="file" name="custom_qris_image" accept="image/*" style="font-size: 12px;">
                        <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">Gunakan gambar QR matrix bersih (persegi kotak) tanpa poster lebar.</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 4px;">Nama Usaha di QRIS</label>
                        <input type="text" name="custom_qris_name" id="customQrisNameInput" value="{{ old('custom_qris_name', $merchant->custom_qris_name) }}" placeholder="Contoh: Berkah Net DANA" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 4px;">NMID QRIS (Opsional)</label>
                        <input type="text" name="custom_nmid" id="customNmidInput" value="{{ old('custom_nmid', $merchant->custom_nmid) }}" placeholder="Contoh: ID1025xxxxxxxx" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 4px;">
                        Kode String QRIS (Otomatis Terdeteksi dari Gambar / Bisa Diisi Manual)
                    </label>
                    <textarea name="custom_qris_payload" id="customQrisPayloadInput" rows="2" placeholder="000201010211... (String EMVCo QRIS)" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 11px; font-family: var(--kp-mono);">{{ old('custom_qris_payload', $merchant->custom_qris_payload) }}</textarea>
                    <div style="font-size: 11px; color: #059669; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-bolt"></i> <b>Fitur Dynamic QRIS (Auto-Nominal)</b>: Dengan string ini, pembeli voucher Hotspot Anda saat scan di m-Banking/DANA akan langsung terkunci nominal harganya (tanpa perlu ketik nominal manual seperti TriPay).
                    </div>
                </div>
            </div>

            <button type="submit" class="kp-btn kp-btn-primary" style="width: 100%; padding: 11px; font-size: 13.5px;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pilihan Mode Gateway
            </button>
        </form>
    </div>

    <!-- CARD 2: TOP UP SALDO DEPOSIT KUOTA FEE -->
    <div class="kp-card" id="section-topup-fee" style="margin-bottom: 0;">
        <div class="kp-card-header">
            <div class="kp-card-title">
                <i class="fa-solid fa-coins" style="color: #d97706;"></i> Top Up Saldo Kuota Deposit Fee
            </div>
            <span style="font-size: 12px; font-weight: 800; color: #d97706; font-family: var(--kp-mono);">
                Sisa: Rp {{ number_format($merchant->fee_deposit_balance, 0, ',', '.') }}
            </span>
        </div>

        <p style="font-size: 12px; color: var(--kp-text-muted); line-height: 1.5; margin-bottom: 14px;">
            Saldo Deposit Fee digunakan untuk memotong biaya admin <b>0.3%</b> saat Anda menggunakan <b>QRIS Milik Sendiri</b>. Saat pelanggan membeli voucher, saldo deposit ini otomatis terpotong tanpa mengurangi uang di rekening Anda.
        </p>

        @if($merchant->fee_deposit_balance <= 0 && $merchant->gateway_mode === 'custom')
            <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; font-size: 12px; color: #991b1b; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px;"></i>
                <div>
                    <b>Peringatan Saldo Kuota Habis:</b> Transaksi pelanggan Anda tidak dapat diproses sampai saldo deposit fee diisi kembali.
                </div>
            </div>
        @endif

        <form action="{{ route('merchant.deposit.topup') }}" method="POST">
            @csrf

            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-main); margin-bottom: 6px;">Pilih Nominal Top Up Saldo Deposit:</label>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 12px;">
                <button type="button" class="kp-btn" style="background: #f8fafc; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 8px 4px; font-size: 11.5px; font-weight: 700;" onclick="setDepositAmount(10000)">
                    Rp 10.000
                </button>
                <button type="button" class="kp-btn" style="background: #f8fafc; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 8px 4px; font-size: 11.5px; font-weight: 700;" onclick="setDepositAmount(25000)">
                    Rp 25.000
                </button>
                <button type="button" class="kp-btn" style="background: #f8fafc; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 8px 4px; font-size: 11.5px; font-weight: 700;" onclick="setDepositAmount(50000)">
                    Rp 50.000
                </button>
                <button type="button" class="kp-btn" style="background: #f8fafc; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 8px 4px; font-size: 11.5px; font-weight: 700;" onclick="setDepositAmount(100000)">
                    Rp 100.000
                </button>
            </div>

            <div style="margin-bottom: 16px;">
                <input type="number" id="depositAmountInput" name="amount" value="25000" min="5000" max="1000000" required placeholder="Masukkan nominal (Min. Rp 5.000)" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 14px; font-family: var(--kp-mono); font-weight: 700;">
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">Top Up Rp 25.000 cukup untuk kuota penjualan voucher hingga <b>Rp 8.333.000</b>!</div>
            </div>

            <button type="submit" class="kp-btn kp-btn-success" style="width: 100%; padding: 11px; font-size: 13.5px;">
                <i class="fa-solid fa-qrcode"></i> Bayar Top Up via QRIS Kurtech &rarr;
            </button>
        </form>
    </div>

</div>

<!-- Recent Transactions -->
<div class="kp-card">
    <div class="kp-card-header">
        <div class="kp-card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--kp-primary);"></i> Transaksi Voucher Terbaru
        </div>
        <a href="{{ route('merchant.api.credentials') }}" style="font-size: 12.5px; color: var(--kp-primary); font-weight: 700; text-decoration: none;">
            Pengaturan API &rarr;
        </a>
    </div>

    @if($recentTransactions->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--kp-border); text-align: left; color: var(--kp-text-muted);">
                        <th style="padding: 10px 8px;">No. Transaksi</th>
                        <th style="padding: 10px 8px;">Layanan / Voucher</th>
                        <th style="padding: 10px 8px;">Mode</th>
                        <th style="padding: 10px 8px;">Nominal Asli</th>
                        <th style="padding: 10px 8px;">Total Dibayar</th>
                        <th style="padding: 10px 8px;">Waktu</th>
                        <th style="padding: 10px 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $trx)
                        <tr style="border-bottom: 1px dashed var(--kp-border);">
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                                #{{ $trx->trx_id }}
                            </td>
                            <td style="padding: 12px 8px; font-weight: 600;">
                                {{ $trx->service_name }}
                            </td>
                            <td style="padding: 12px 8px;">
                                @if($trx->is_deposit_topup)
                                    <span style="background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 6px; font-size: 10.5px; font-weight: 800;">Top Up Deposit</span>
                                @elseif($trx->gateway_mode === 'custom')
                                    <span style="background: #e0e7ff; color: #3730a3; padding: 2px 7px; border-radius: 6px; font-size: 10.5px; font-weight: 800;">QRIS Sendiri</span>
                                @else
                                    <span style="background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 6px; font-size: 10.5px; font-weight: 800;">Gateway Kurtech</span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700;">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-success-dark);">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; color: var(--kp-text-muted); font-size: 12px;">
                                {{ $trx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding: 12px 8px;">
                                @if($trx->status === 'paid')
                                    <span style="background: var(--kp-success-light); color: var(--kp-success-dark); border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;">
                                        <i class="fa-solid fa-check"></i> Lunas
                                    </span>
                                @else
                                    <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;">
                                        <i class="fa-solid fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 40px 10px; color: var(--kp-text-muted);">
            <div style="font-size: 36px; margin-bottom: 10px; opacity: 0.4;">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div style="font-weight: 700; font-size: 14px;">Belum Ada Transaksi</div>
            <p style="font-size: 12.5px; margin-top: 4px;">Hubungkan Mikhmon atau Billing Anda dengan API Key untuk mulai menerima pembayaran otomatis!</p>
            <a href="{{ route('merchant.api.credentials') }}" class="kp-btn kp-btn-primary" style="margin-top: 14px;">
                <i class="fa-solid fa-code"></i> Ambil Kredensial API
            </a>
        </div>
    @endif
</div>

<script src="{{ asset('js/jsqr.js') }}"></script>
<script>
function toggleCustomFields(isCustom) {
    const container = document.getElementById('customQrisContainer');
    if (container) {
        container.style.display = isCustom ? 'block' : 'none';
    }
}

function setDepositAmount(amount) {
    const input = document.getElementById('depositAmountInput');
    if (input) {
        input.value = amount;
    }
}

// Auto-detect QRIS string & NMID from uploaded QRIS image using jsQR
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[name="custom_qris_image"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);

                    try {
                        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        if (typeof jsQR !== 'undefined') {
                            const qr = jsQR(imgData.data, imgData.width, imgData.height);
                            if (qr && qr.data && qr.data.startsWith('000201')) {
                                const payloadInput = document.getElementById('customQrisPayloadInput');
                                if (payloadInput) {
                                    payloadInput.value = qr.data;
                                }

                                // Auto extract NMID
                                const nmidMatch = qr.data.match(/0215(ID[0-9A-Z]+)/);
                                if (nmidMatch) {
                                    const nmidInput = document.getElementById('customNmidInput');
                                    if (nmidInput && !nmidInput.value) {
                                        nmidInput.value = nmidMatch[1];
                                    }
                                }

                                // Auto extract Merchant Name (Tag 59)
                                const nameMatch = qr.data.match(/59([0-9]{2})([^0-9]+)/);
                                if (nameMatch) {
                                    const nameLen = parseInt(nameMatch[1], 10);
                                    const nameStart = qr.data.indexOf('59' + nameMatch[1]) + 4;
                                    const extractedName = qr.data.substr(nameStart, nameLen);
                                    const nameInput = document.getElementById('customQrisNameInput');
                                    if (nameInput && !nameInput.value) {
                                        nameInput.value = extractedName;
                                    }
                                }

                                alert('✨ QRIS Berhasil Dibaca!\nString QRIS dan NMID otomatis terisi untuk fitur Dynamic QRIS (Auto-Nominal).');
                            }
                        }
                    } catch (err) {
                        console.error('QR decode error:', err);
                    }
                };
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endsection
