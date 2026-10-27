@extends('layouts.app')

@section('title', 'Daftar Tenant / Merchant')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main);">Daftar Tenant Kurtech Pay</h2>
    <p style="font-size: 13px; color: var(--kp-text-muted);">Kelola toko, pemilik WiFi RT-RW Net, dan saldo yang tersimpan.</p>
</div>

<div class="kp-card">
    @if($merchants->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--kp-border); text-align: left; color: var(--kp-text-muted);">
                        <th style="padding: 10px 8px;">Kode</th>
                        <th style="padding: 10px 8px;">Nama Usaha / Tenant</th>
                        <th style="padding: 10px 8px;">Kontak WhatsApp</th>
                        <th style="padding: 10px 8px;">Saldo Dompet</th>
                        <th style="padding: 10px 8px;">Rekening Pencairan</th>
                        <th style="padding: 10px 8px;">Total Transaksi</th>
                        <th style="padding: 10px 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($merchants as $m)
                        <tr style="border-bottom: 1px dashed var(--kp-border);">
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                                {{ $m->merchant_code }}
                            </td>
                            <td style="padding: 12px 8px; font-weight: 700;">
                                {{ $m->business_name }}<br>
                                <span style="font-size: 11.5px; color: var(--kp-text-muted); font-weight: normal;">{{ $m->user ? $m->user->name : '-' }}</span>
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-size: 12.5px;">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->user ? $m->user->phone : '') }}" target="_blank" style="color: var(--kp-success); text-decoration: none;">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $m->user ? $m->user->phone : '-' }}
                                </a>
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 800; font-size: 14px; color: var(--kp-success-dark);">
                                Rp {{ number_format($m->balance, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-size: 12px;">
                                @if(!empty($m->bank_account))
                                    <b>{{ $m->bank_name }}</b> - <span style="font-family: var(--kp-mono);">{{ $m->bank_account }}</span><br>
                                    <span style="color: var(--kp-text-muted);">a.n {{ $m->bank_holder }}</span>
                                @else
                                    <span style="color: var(--kp-text-muted); font-style: italic;">Belum diisi</span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; font-weight: 700;">
                                {{ $m->transactions_count }} Transaksi
                            </td>
                            <td style="padding: 12px 8px;">
                                <span style="background: var(--kp-success-light); color: var(--kp-success-dark); border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 16px;">
            {{ $merchants->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 30px; color: var(--kp-text-muted);">Belum ada merchant terdaftar.</div>
    @endif
</div>
@endsection
