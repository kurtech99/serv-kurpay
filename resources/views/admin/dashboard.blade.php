@extends('layouts.app')

@section('title', 'Master Panel SuperAdmin')

@section('content')
<div style="margin-bottom: 22px;">
    <h2 style="font-size: 22px; font-weight: 800; color: var(--kp-text-main);">Master Panel Gateway</h2>
    <p style="font-size: 13px; color: var(--kp-text-muted);">Monitoring perputaran dana, pencairan tenant, dan keuntungan fee Kurtech.</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Keuntungan Fee Kurtech -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-success); background: #f0fdf4;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 800; color: #166534; text-transform: uppercase;">Total Keuntungan Fee Kurtech</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #dcfce7; color: var(--kp-success); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: #15803d; font-family: var(--kp-mono);">
            Rp {{ number_format($totalKurtechProfit, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: #166534; margin-top: 4px;">
            Akumulasi fee penarikan Rp 10.000/tarik
        </div>
    </div>

    <!-- Total Saldo Tenant Tersimpan -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-warning);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Saldo Milik Tenant</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #fef3c7; color: var(--kp-warning); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main); font-family: var(--kp-mono);">
            Rp {{ number_format($totalMerchantBalance, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Total saldo yang mengendap di DANA Kurtech
        </div>
    </div>

    <!-- Total Volume Penjualan Voucher -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid var(--kp-primary);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Total Transaksi Voucher</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--kp-primary-light); color: var(--kp-primary); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main); font-family: var(--kp-mono);">
            Rp {{ number_format($totalVolume, 0, ',', '.') }}
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Total volume transaksi lunas
        </div>
    </div>

    <!-- Total Tenant Aktif -->
    <div class="kp-card" style="margin-bottom: 0; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--kp-text-muted); text-transform: uppercase;">Total Tenant / Toko</div>
            <div style="width: 34px; height: 34px; border-radius: 10px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                <i class="fa-solid fa-store"></i>
            </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--kp-text-main);">
            {{ $totalMerchants }} <span style="font-size: 14px; font-weight: 600; color: var(--kp-text-muted);">Tenant</span>
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 4px;">
            Pengguna aktif Kurtech Pay
        </div>
    </div>
</div>

<!-- Pending Withdrawals Section -->
<div class="kp-card">
    <div class="kp-card-header">
        <div class="kp-card-title">
            <i class="fa-solid fa-bell" style="color: var(--kp-danger);"></i> Permintaan Pencairan Dana Menunggu Transfer
            @if($pendingWithdrawals->count() > 0)
                <span style="background: var(--kp-danger); color: #fff; font-size: 11px; padding: 2px 7px; border-radius: 999px;">{{ $pendingWithdrawals->count() }}</span>
            @endif
        </div>
        <a href="{{ route('admin.withdrawals') }}" style="font-size: 12.5px; color: var(--kp-primary); font-weight: 700; text-decoration: none;">
            Lihat Semua &rarr;
        </a>
    </div>

    @if($pendingWithdrawals->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--kp-border); text-align: left; color: var(--kp-text-muted);">
                        <th style="padding: 10px 8px;">ID</th>
                        <th style="padding: 10px 8px;">Tenant</th>
                        <th style="padding: 10px 8px;">Nominal Tarik</th>
                        <th style="padding: 10px 8px;">Fee Kurtech</th>
                        <th style="padding: 10px 8px; color: var(--kp-success-dark);">Harus Ditransfer</th>
                        <th style="padding: 10px 8px;">Rekening Tujuan</th>
                        <th style="padding: 10px 8px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingWithdrawals as $wd)
                        <tr style="border-bottom: 1px dashed var(--kp-border);">
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                                #{{ $wd->withdrawal_id }}
                                @if($wd->is_auto)
                                    <span style="font-size: 9px; background: #e0e7ff; color: #4338ca; padding: 2px 4px; border-radius: 4px;">Otomatis</span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; font-weight: 700;">
                                {{ $wd->merchant->business_name }}<br>
                                <span style="font-size: 11px; color: var(--kp-text-muted); font-weight: normal;">{{ $wd->merchant->user->phone }}</span>
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700;">
                                Rp {{ number_format($wd->amount_requested, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-success-dark);">
                                +Rp {{ number_format($wd->admin_fee, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 800; font-size: 14.5px; color: var(--kp-primary);">
                                Rp {{ number_format($wd->amount_transferred, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px;">
                                <b>{{ $wd->bank_target }}</b><br>
                                <span style="font-family: var(--kp-mono); color: var(--kp-text-main); font-weight: 700;">{{ $wd->account_target }}</span><br>
                                <span style="font-size: 11.5px; color: var(--kp-text-muted);">a.n {{ $wd->holder_target }}</span>
                            </td>
                            <td style="padding: 12px 8px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <form action="{{ route('admin.withdrawals.approve', $wd->id) }}" method="POST" onsubmit="return confirm('Apakah Anda sudah mentransfer Rp {{ number_format($wd->amount_transferred, 0, ',', '.') }} ke rekening tenant?');">
                                        @csrf
                                        <button type="submit" class="kp-btn kp-btn-success" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fa-solid fa-check"></i> Sudah Ditransfer
                                        </button>
                                    </form>
                                    <button type="button" onclick="showRejectModal('{{ $wd->id }}', '{{ $wd->withdrawal_id }}')" class="kp-btn kp-btn-outline" style="padding: 6px 10px; font-size: 12px; color: var(--kp-danger);">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 32px 10px; color: var(--kp-text-muted);">
            <div style="font-size: 30px; margin-bottom: 6px; opacity: 0.4;">
                <i class="fa-solid fa-circle-check" style="color: var(--kp-success);"></i>
            </div>
            <div style="font-weight: 700; font-size: 13.5px;">Semua Permintaan Penarikan Beres!</div>
            <p style="font-size: 12px; margin-top: 2px;">Tidak ada permintaan pencairan dana yang tertunda saat ini.</p>
        </div>
    @endif
</div>

<!-- Modal Reject -->
<div id="modalReject" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 16px; padding: 22px; max-width: 380px; width: 90%;">
        <h4 style="font-size: 15px; font-weight: 800; margin-bottom: 8px;">Tolak Penarikan Dana</h4>
        <p style="font-size: 12px; color: var(--kp-text-muted); margin-bottom: 14px;">Saldo akan dikembalikan utuh ke akun merchant.</p>
        <form id="formReject" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Alasan Penolakan</label>
                <input type="text" name="reason" required placeholder="Contoh: Nomor rekening tidak valid" 
                       style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1.5px solid var(--kp-border); font-size: 13px;">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('modalReject').style.display='none';" class="kp-btn kp-btn-outline">Batal</button>
                <button type="submit" class="kp-btn" style="background: var(--kp-danger); color: #fff;">Tolak Penarikan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showRejectModal(id, code) {
        const form = document.getElementById('formReject');
        form.action = '/admin/withdrawals/' + id + '/reject';
        document.getElementById('modalReject').style.display = 'flex';
    }
</script>
@endsection
