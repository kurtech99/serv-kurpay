@extends('layouts.app')

@section('title', 'Manajemen Pencairan Dana Tenant')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main);">Semua Penarikan Dana</h2>
        <p style="font-size: 13px; color: var(--kp-text-muted);">Daftar riwayat dan permintaan transfer saldo tenant.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.withdrawals', ['status' => 'all']) }}" class="kp-btn {{ $status === 'all' ? 'kp-btn-primary' : 'kp-btn-outline' }}" style="padding: 6px 12px; font-size: 12px;">Semua</a>
        <a href="{{ route('admin.withdrawals', ['status' => 'pending']) }}" class="kp-btn {{ $status === 'pending' ? 'kp-btn-primary' : 'kp-btn-outline' }}" style="padding: 6px 12px; font-size: 12px;">Menunggu Transfer</a>
        <a href="{{ route('admin.withdrawals', ['status' => 'completed']) }}" class="kp-btn {{ $status === 'completed' ? 'kp-btn-primary' : 'kp-btn-outline' }}" style="padding: 6px 12px; font-size: 12px;">Selesai</a>
        <a href="{{ route('admin.withdrawals', ['status' => 'rejected']) }}" class="kp-btn {{ $status === 'rejected' ? 'kp-btn-primary' : 'kp-btn-outline' }}" style="padding: 6px 12px; font-size: 12px;">Ditolak</a>
    </div>
</div>

<div class="kp-card">
    @if($withdrawals->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--kp-border); text-align: left; color: var(--kp-text-muted);">
                        <th style="padding: 10px 8px;">ID Penarikan</th>
                        <th style="padding: 10px 8px;">Tenant</th>
                        <th style="padding: 10px 8px;">Nominal Tarik</th>
                        <th style="padding: 10px 8px;">Fee Kurtech</th>
                        <th style="padding: 10px 8px; color: var(--kp-success-dark);">Harus Ditransfer</th>
                        <th style="padding: 10px 8px;">Rekening Tujuan</th>
                        <th style="padding: 10px 8px;">Status</th>
                        <th style="padding: 10px 8px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawals as $wd)
                        <tr style="border-bottom: 1px dashed var(--kp-border);">
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                                #{{ $wd->withdrawal_id }}
                                @if($wd->is_auto)
                                    <span style="font-size: 9px; background: #e0e7ff; color: #4338ca; padding: 2px 4px; border-radius: 4px;">Otomatis</span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; font-weight: 700;">
                                {{ $wd->merchant->business_name }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700;">
                                Rp {{ number_format($wd->amount_requested, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-success-dark);">
                                +Rp {{ number_format($wd->admin_fee, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-family: var(--kp-mono); font-weight: 800; color: var(--kp-primary); font-size: 14px;">
                                Rp {{ number_format($wd->amount_transferred, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 8px; font-size: 12.5px;">
                                <b>{{ $wd->bank_target }}</b> - <span style="font-family: var(--kp-mono); font-weight: 700;">{{ $wd->account_target }}</span><br>
                                <span style="color: var(--kp-text-muted);">a.n {{ $wd->holder_target }}</span>
                            </td>
                            <td style="padding: 12px 8px;">
                                @if($wd->status === 'completed')
                                    <span style="background: var(--kp-success-light); color: var(--kp-success-dark); border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;">
                                        <i class="fa-solid fa-check"></i> Selesai
                                    </span>
                                @elseif($wd->status === 'rejected')
                                    <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;">
                                        <i class="fa-solid fa-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 800;">
                                        <i class="fa-solid fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; text-align: right;">
                                @if($wd->status === 'pending')
                                    <form action="{{ route('admin.withdrawals.approve', $wd->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tandai penarikan Rp {{ number_format($wd->amount_transferred, 0, ',', '.') }} sudah selesai ditransfer?');">
                                        @csrf
                                        <button type="submit" class="kp-btn kp-btn-success" style="padding: 5px 10px; font-size: 11.5px;">
                                            <i class="fa-solid fa-check"></i> Sudah Transfer
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 11px; color: var(--kp-text-muted);">{{ $wd->updated_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $withdrawals->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 36px 10px; color: var(--kp-text-muted);">
            <div style="font-size: 28px; margin-bottom: 8px; opacity: 0.4;"><i class="fa-solid fa-inbox"></i></div>
            <div style="font-weight: 700;">Tidak Ada Data Penarikan</div>
        </div>
    @endif
</div>
@endsection
