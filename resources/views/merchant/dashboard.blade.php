@extends('layouts.app')

@section('title', 'Dashboard Merchant')

@section('content')
<!-- Header Banner -->
<div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 20px; padding: 26px 24px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.25); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
        <div style="font-size: 13px; font-weight: 700; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Selamat Datang,</div>
        <h1 style="font-size: 24px; font-weight: 800; margin-top: 2px;">{{ $merchant->business_name }}</h1>
        <div style="font-size: 12.5px; opacity: 0.85; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
            <span>ID Merchant: <b>{{ $merchant->merchant_code }}</b></span>
            <span>•</span>
            <span>Status: <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 999px;">Aktif</span></span>
        </div>
    </div>
    <div>
        <a href="{{ route('merchant.withdrawal') }}" class="kp-btn" style="background: #ffffff; color: #0284c7; padding: 12px 20px; font-size: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
            <i class="fa-solid fa-money-bill-transfer"></i> Tarik Saldo Sekarang
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Saldo Dompet -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-success);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Saldo Tersedia</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--kp-success-light); color: var(--kp-success); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-success-dark); font-family: var(--kp-mono);">
            Rp {{ number_format($merchant->balance, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Siap ditarik kapan saja ke rekening
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

    <!-- Pendapatan Bulan Ini -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-warning);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Omset Bulan Ini</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #fef3c7; color: var(--kp-warning); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main); font-family: var(--kp-mono);">
            Rp {{ number_format($monthRevenue, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Total omset {{ date('F Y') }}
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
@endsection
