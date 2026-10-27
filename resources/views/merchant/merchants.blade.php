@extends('layouts.app')

@section('title', 'Daftar Merchant & Router')

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
    <div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--kp-text-main); letter-spacing: -0.3px;">
            <i class="fa-solid fa-store" style="color: var(--kp-primary); margin-right: 6px;"></i> Daftar Merchant &amp; Router
        </h2>
        <p style="font-size: 13.5px; color: var(--kp-text-muted); margin-top: 3px;">
            Setiap router MikroTik memiliki Kunci API dan Webhook tersendiri agar transaksi tidak saling tertukar.
        </p>
    </div>
    <button type="button" onclick="openModalTambah()" class="kp-btn kp-btn-primary" style="padding: 10px 20px; font-size: 14px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
        <i class="fa-solid fa-plus"></i> Tambah Merchant
    </button>
</div>

<!-- Alert Notif -->
@if(session('success'))
    <div class="kp-alert kp-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="kp-alert kp-alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Merchant List Table -->
<div class="kp-card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid var(--kp-border); color: var(--kp-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 14px 18px;">Kode</th>
                    <th style="padding: 14px 18px;">Nama Merchant / Router</th>
                    <th style="padding: 14px 18px;">Mode Gateway</th>
                    <th style="padding: 14px 18px;">Transaksi</th>
                    <th style="padding: 14px 18px;">Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchants as $m)
                    @php $isActiveNow = ($activeMerchant->id === $m->id); @endphp
                    <tr style="border-bottom: 1px solid var(--kp-border); transition: background 0.15s ease; {{ $isActiveNow ? 'background: #f0fdf4;' : '' }}">
                        <td style="padding: 16px 18px; font-family: var(--kp-mono); font-weight: 700; color: var(--kp-primary);">
                            {{ $m->merchant_code }}
                        </td>
                        <td style="padding: 16px 18px;">
                            <div style="font-weight: 700; font-size: 14px; color: var(--kp-text-main); display: flex; align-items: center; gap: 8px;">
                                {{ $m->business_name }}
                                @if($isActiveNow)
                                    <span style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 999px;">
                                        Sedang Dibuka
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 11.5px; color: var(--kp-text-muted); font-family: var(--kp-mono); margin-top: 3px;">
                                API: {{ substr($m->api_key, 0, 16) }}...
                            </div>
                        </td>
                        <td style="padding: 16px 18px;">
                            @if($m->isCustomGateway())
                                <span style="background: #fef3c7; color: #92400e; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px;">
                                    <i class="fa-solid fa-qrcode"></i> QRIS Sendiri
                                </span>
                            @else
                                <span style="background: #e0f2fe; color: #0369a1; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px;">
                                    <i class="fa-solid fa-shield-halved"></i> Sewa Kurtech
                                </span>
                            @endif
                        </td>
                        <td style="padding: 16px 18px;">
                            <div style="font-weight: 700; color: var(--kp-text-main);">
                                {{ number_format($m->total_paid_tx ?? 0, 0, ',', '.') }} Lunas
                            </div>
                            <div style="font-size: 11.5px; color: var(--kp-success); font-family: var(--kp-mono); font-weight: 700;">
                                Rp {{ number_format($m->total_revenue ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td style="padding: 16px 18px;">
                            <span style="background: #dcfce7; color: #166534; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px;">
                                ● {{ ucfirst($m->status) }}
                            </span>
                        </td>
                        <td style="padding: 16px 18px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                @if(!$isActiveNow)
                                    <a href="{{ route('merchant.merchants.switch', $m->id) }}" class="kp-btn kp-btn-outline" style="padding: 6px 12px; font-size: 12px; color: var(--kp-primary);" title="Buka Dashboard Router Ini">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk
                                    </a>
                                @endif
                                <a href="{{ route('merchant.api.credentials', ['id' => $m->id]) }}" class="kp-btn kp-btn-outline" style="padding: 6px 12px; font-size: 12px;" title="Lihat Kunci API Router Ini">
                                    <i class="fa-solid fa-key" style="color: #f59e0b;"></i> Kunci API
                                </a>
                                @if($merchants->count() > 1)
                                    <form action="{{ route('merchant.merchants.delete', $m->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus merchant {{ $m->business_name }}?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kp-btn kp-btn-outline" style="padding: 6px 10px; font-size: 12px; color: var(--kp-danger);" title="Hapus Merchant">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: var(--kp-text-muted);">
                            Belum ada merchant. Klik tombol <b>+ Tambah Merchant</b> untuk membuat router pertama Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Merchant -->
<div id="modalTambahMerchant" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 20px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: scaleUp 0.2s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid var(--kp-border);">
            <div style="font-size: 17px; font-weight: 800; color: var(--kp-text-main);">
                <i class="fa-solid fa-plus" style="color: var(--kp-primary);"></i> Tambah Merchant / Router Baru
            </div>
            <button type="button" onclick="closeModalTambah()" style="background: none; border: none; font-size: 18px; color: var(--kp-text-muted); cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('merchant.merchants.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">Nama Merchant / Router MikroTik</label>
                <input type="text" name="business_name" required placeholder="Contoh: Router Arka Cibiuk atau Hotspot Lapangan" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; outline: none;">
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">Beri nama agar Anda mudah membedakan router ini di daftar merchant.</div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">Pilihan Mode Gateway Pembayaran</label>
                <select name="gateway_mode" style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; outline: none; background: #fff;">
                    <option value="kurtech">Sewa Gateway Kurtech (Dana ditampung di dompet KurPay, bisa ditarik kapan saja)</option>
                    <option value="custom">Pakai QRIS Sendiri (Uang 100% langsung ke DANA pribadi Anda, potong fee 0.3% dari kuota deposit)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 5px;">URL Webhook Callback (Opsional / Bisa Diisi Nanti)</label>
                <input type="url" name="webhook_url" placeholder="https://billing.kurtech.my.id/billing/webhook.php?session=sesi&gateway=kurpay" 
                       style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13px; font-family: var(--kp-mono); outline: none;">
                <div style="font-size: 11px; color: var(--kp-text-muted); margin-top: 4px;">Bisa disalin nanti dari tab Rekening &amp; Gateway di Billing Kurtech.</div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeModalTambah()" class="kp-btn kp-btn-outline">Batal</button>
                <button type="submit" class="kp-btn kp-btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan &amp; Buat Kunci API
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambah() {
    document.getElementById('modalTambahMerchant').style.display = 'flex';
}
function closeModalTambah() {
    document.getElementById('modalTambahMerchant').style.display = 'none';
}
</script>
@endsection
