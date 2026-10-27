@extends('layouts.app')

@section('title', 'Penarikan Dana (Tarik Saldo)')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main);">Penarikan Dana (Tarik Saldo)</h2>
    <p style="font-size: 13px; color: var(--kp-text-muted);">Tarik saldo pendapatan voucher hotspot Anda langsung ke rekening bank atau e-wallet.</p>
</div>

<!-- Notice Jam Operasional & Ketentuan Simpel -->
<div style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1.5px solid #bfdbfe; border-radius: 16px; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(37,99,235,0.06);">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37,99,235,0.3);">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div style="font-size: 15px; font-weight: 800; color: #1e3a8a; margin-bottom: 4px;">
                    Jadwal Penarikan: Pukul {{ $openTime }} – {{ $closeTime }} WIB
                </div>
                <div style="font-size: 13px; color: #334155; line-height: 1.5;">
                    • Biaya Admin: <b style="color: #dc2626;">Rp {{ number_format($adminFee, 0, ',', '.') }} (Flat)</b> per penarikan.<br>
                    • Minimal Penarikan: <b>Rp {{ number_format($minWithdraw, 0, ',', '.') }}</b> | Maksimal: <b>Rp {{ number_format($maxWithdraw, 0, ',', '.') }}</b> per transaksi.<br>
                    • Pengajuan antara pukul <b>{{ $openTime }} - {{ $closeTime }} WIB</b> akan diproses transfer oleh admin pada hari yang sama.
                </div>
            </div>
        </div>
        <div>
            @if($isOpenNow)
                <span style="background: #dcfce7; color: #166534; border: 1.5px solid #86efac; padding: 6px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle" style="font-size: 8px; color: #22c55e;"></i> Jam Operasional Buka
                </span>
            @else
                <span style="background: #fef2f2; color: #991b1b; border: 1.5px solid #fca5a5; padding: 6px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-clock-rotate-left"></i> Luar Jam Operasional (Diproses Pukul 08:00)
                </span>
            @endif
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">

    <!-- 1. KARTU REKENING TUJUAN PENCAIRAN -->
    <div class="kp-card" style="margin-bottom: 0;">
        <div class="kp-card-header">
            <div class="kp-card-title">
                <i class="fa-solid fa-building-columns" style="color: var(--kp-primary);"></i> 1. Rekening Tujuan Pencairan
            </div>
            @if(!empty($merchant->bank_account))
                <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                    <i class="fa-solid fa-check-circle"></i> Terdaftar
                </span>
            @endif
        </div>

        <form action="{{ route('merchant.bank.update') }}" method="POST">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">Pilih Bank atau E-Wallet</label>
                <select name="bank_name" required style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none; background: #fff;">
                    <option value="Bank BCA" {{ $merchant->bank_name === 'Bank BCA' ? 'selected' : '' }}>Bank BCA</option>
                    <option value="Bank BRI" {{ $merchant->bank_name === 'Bank BRI' ? 'selected' : '' }}>Bank BRI</option>
                    <option value="Bank Mandiri" {{ $merchant->bank_name === 'Bank Mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                    <option value="Bank BNI" {{ $merchant->bank_name === 'Bank BNI' ? 'selected' : '' }}>Bank BNI</option>
                    <option value="Bank BSI" {{ $merchant->bank_name === 'Bank BSI' ? 'selected' : '' }}>Bank BSI (Syariah)</option>
                    <option value="Bank Jago" {{ $merchant->bank_name === 'Bank Jago' ? 'selected' : '' }}>Bank Jago</option>
                    <option value="Bank Seabank" {{ $merchant->bank_name === 'Bank Seabank' ? 'selected' : '' }}>SeaBank</option>
                    <option value="DANA" {{ $merchant->bank_name === 'DANA' ? 'selected' : '' }}>DANA</option>
                    <option value="GoPay" {{ $merchant->bank_name === 'GoPay' ? 'selected' : '' }}>GoPay</option>
                    <option value="OVO" {{ $merchant->bank_name === 'OVO' ? 'selected' : '' }}>OVO</option>
                    <option value="ShopeePay" {{ $merchant->bank_name === 'ShopeePay' ? 'selected' : '' }}>ShopeePay</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">Nomor Rekening / No HP Akun E-Wallet</label>
                <input type="text" name="bank_account" value="{{ old('bank_account', $merchant->bank_account) }}" required placeholder="Contoh: 7285513366 atau 0812345678" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; outline: none;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">Nama Pemilik Rekening Sesuai Buku Tabungan / KTP</label>
                <input type="text" name="bank_holder" value="{{ old('bank_holder', $merchant->bank_holder) }}" required placeholder="Contoh: ANGGI KURNIAWAN" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-font); outline: none;">
            </div>

            <button type="submit" class="kp-btn kp-btn-outline" style="width: 100%; justify-content: center; padding: 11px;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Rekening Pencairan
            </button>
        </form>
    </div>

    <!-- 2. KARTU PENGAJUAN TARIK SALDO -->
    <div class="kp-card" style="margin-bottom: 0;">
        <div class="kp-card-header">
            <div class="kp-card-title">
                <i class="fa-solid fa-hand-holding-dollar" style="color: var(--kp-success);"></i> 2. Form Tarik Saldo
            </div>
            <div class="kp-badge-balance">
                Saldo: Rp {{ number_format($merchant->balance, 0, ',', '.') }}
            </div>
        </div>

        <form action="{{ route('merchant.withdrawal.submit') }}" method="POST">
            @csrf

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">
                    Nominal Tarik Saldo (Rp)
                </label>
                <input type="number" id="inputWithdrawAmount" name="amount" min="{{ $minWithdraw }}" max="{{ $maxWithdraw }}" step="1000" required 
                       value="{{ old('amount', min($merchant->balance, $maxWithdraw)) }}"
                       placeholder="Contoh: 100000" oninput="calculateWithdrawal()"
                       style="width: 100%; padding: 12px 14px; border-radius: 12px; border: 2px solid var(--kp-primary); font-size: 17px; font-family: var(--kp-mono); font-weight: 800; color: var(--kp-primary); outline: none;">
                <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: var(--kp-text-muted); margin-top: 5px;">
                    <span>Min: <b>Rp {{ number_format($minWithdraw, 0, ',', '.') }}</b></span>
                    <span>Maks: <b>Rp {{ number_format($maxWithdraw, 0, ',', '.') }}</b></span>
                </div>
            </div>

            <!-- Tombol Cepat Pilihan Nominal -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
                @foreach([50000, 100000, 200000, 300000, 500000] as $preset)
                    @if($preset <= $merchant->balance)
                        <button type="button" onclick="setWithdrawAmount({{ $preset }})" 
                                style="background: #f1f5f9; border: 1px solid var(--kp-border); border-radius: 8px; padding: 4px 10px; font-size: 11.5px; font-weight: 700; font-family: var(--kp-mono); cursor: pointer; color: var(--kp-text-main);">
                            Rp {{ number_format($preset, 0, ',', '.') }}
                        </button>
                    @endif
                @endforeach
                @if($merchant->balance >= $minWithdraw)
                    <button type="button" onclick="setWithdrawAmount({{ min($merchant->balance, $maxWithdraw) }})" 
                            style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 4px 10px; font-size: 11.5px; font-weight: 700; font-family: var(--kp-mono); cursor: pointer; color: var(--kp-primary);">
                        Tarik Maksimal
                    </button>
                @endif
            </div>

            <!-- Kalkulator Rincian Penerimaan -->
            <div style="background: #f8fafc; border: 1.5px solid var(--kp-border); border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--kp-text-muted);">Biaya Admin Kurtech:</span>
                    <span style="font-weight: 700; color: #dc2626; font-family: var(--kp-mono);">Rp {{ number_format($adminFee, 0, ',', '.') }} (Flat)</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed var(--kp-border); align-items: baseline;">
                    <span style="font-weight: 700; color: var(--kp-text-main);">Dana Ditransfer ke Rekening:</span>
                    <span id="labelNetTransfer" style="font-size: 17px; font-weight: 900; color: var(--kp-success-dark); font-family: var(--kp-mono);">
                        Rp 0
                    </span>
                </div>
            </div>

            @if(empty($merchant->bank_account))
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 12px; font-size: 12px; color: #92400e; margin-bottom: 14px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Harap isi dan simpan data rekening Anda di formulir sebelah kiri terlebih dahulu.
                </div>
            @endif

            <button type="submit" class="kp-btn kp-btn-success" style="width: 100%; justify-content: center; padding: 13px; font-size: 14.5px; font-weight: 800;" 
                    {{ ($merchant->balance < $minWithdraw || empty($merchant->bank_account)) ? 'disabled' : '' }}>
                <i class="fa-solid fa-paper-plane"></i> Ajukan Penarikan Sekarang
            </button>

            @if($merchant->balance < $minWithdraw)
                <div style="font-size: 11.5px; color: var(--kp-danger); text-align: center; margin-top: 8px; font-weight: 600;">
                    * Saldo belum mencapai batas minimal Rp {{ number_format($minWithdraw, 0, ',', '.') }}
                </div>
            @endif
        </form>
    </div>

</div>

<!-- 3. TABEL RIWAYAT PENARIKAN DANA -->
<div class="kp-card">
    <div class="kp-card-header">
        <div class="kp-card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--kp-primary);"></i> Riwayat Penarikan Dana Anda
        </div>
        <span style="font-size: 12px; color: var(--kp-text-muted);">Total: {{ $withdrawals->total() }} pengajuan</span>
    </div>

    @if($withdrawals->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--kp-border); text-align: left; color: var(--kp-text-muted);">
                        <th style="padding: 10px 10px;">ID Penarikan</th>
                        <th style="padding: 10px 10px;">Nominal Tarik</th>
                        <th style="padding: 10px 10px;">Biaya Admin</th>
                        <th style="padding: 10px 10px;">Dana Ditransfer</th>
                        <th style="padding: 10px 10px;">Rekening Tujuan</th>
                        <th style="padding: 10px 10px;">Waktu Pengajuan</th>
                        <th style="padding: 10px 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawals as $wd)
                        <tr style="border-bottom: 1px dashed var(--kp-border);">
                            <td style="padding: 12px 10px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                                #{{ $wd->withdrawal_id }}
                            </td>
                            <td style="padding: 12px 10px; font-family: var(--kp-mono); font-weight: 700;">
                                Rp {{ number_format($wd->amount_requested, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 10px; font-family: var(--kp-mono); color: #dc2626;">
                                -Rp {{ number_format($wd->admin_fee, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 10px; font-family: var(--kp-mono); font-weight: 800; color: var(--kp-success-dark);">
                                Rp {{ number_format($wd->amount_transferred, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 10px; font-size: 12.5px;">
                                <b>{{ $wd->bank_target }}</b><br>
                                <span style="font-family: var(--kp-mono); color: var(--kp-text-muted);">{{ $wd->account_target }} ({{ $wd->holder_target }})</span>
                            </td>
                            <td style="padding: 12px 10px; font-size: 12px; color: var(--kp-text-muted);">
                                {{ $wd->created_at->format('d/m/Y H:i') }} WIB
                            </td>
                            <td style="padding: 12px 10px;">
                                @if($wd->status === 'completed')
                                    <span style="background: var(--kp-success-light); color: var(--kp-success-dark); border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-check"></i> Sudah Ditransfer
                                    </span>
                                @elseif($wd->status === 'rejected')
                                    <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;" title="{{ $wd->notes }}">
                                        <i class="fa-solid fa-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-hourglass-half"></i> Menunggu Transfer Admin
                                    </span>
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
        <div style="text-align: center; padding: 36px 12px; color: var(--kp-text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px; opacity: 0.35;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div style="font-weight: 600; font-size: 13.5px;">Belum Ada Riwayat Penarikan Dana</div>
            <div style="font-size: 12px; margin-top: 4px;">Setelah Anda mengajukan penarikan saldo, riwayatnya akan tampil di sini.</div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const fee = {{ $adminFee }};

    function calculateWithdrawal() {
        const input = document.getElementById('inputWithdrawAmount');
        const val = parseFloat(input.value) || 0;
        const net = Math.max(0, val - fee);
        document.getElementById('labelNetTransfer').innerText = 'Rp ' + net.toLocaleString('id-ID');
    }

    function setWithdrawAmount(amount) {
        document.getElementById('inputWithdrawAmount').value = amount;
        calculateWithdrawal();
    }

    // Hitung pertama kali saat halaman dimuat
    document.addEventListener('DOMContentLoaded', calculateWithdrawal);
</script>
@endsection
