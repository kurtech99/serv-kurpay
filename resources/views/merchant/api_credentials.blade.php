@extends('layouts.app')

@section('title', 'Kredensial & Integrasi API')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--kp-text-main);">Integrasi API &amp; Kredensial</h2>
    <p style="font-size: 13px; color: var(--kp-text-muted);">Gunakan kredensial ini untuk menghubungkan Mikhmon Online, Billing Kurtech, atau Aplikasi Web Anda.</p>
</div>

<!-- API Keys Card -->
<div class="kp-card">
    <div class="kp-card-header">
        <div class="kp-card-title">
            <i class="fa-solid fa-key" style="color: var(--kp-primary);"></i> Kunci API Toko Anda
        </div>
        <form action="{{ route('merchant.api.regenerate') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengganti API Key? Integrasi lama yang masih menggunakan key lama akan terputus.');">
            @csrf
            <button type="submit" class="kp-btn kp-btn-outline" style="padding: 6px 12px; font-size: 12px; color: var(--kp-danger);">
                <i class="fa-solid fa-arrows-rotate"></i> Buat Kunci Baru
            </button>
        </form>
    </div>

    <!-- Merchant Code -->
    <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 4px;">Kode Merchant (Merchant Code)</label>
        <div style="display: flex; gap: 8px;">
            <input type="text" readonly value="{{ $merchant->merchant_code }}" id="inputMerchantCode"
                   style="flex: 1; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 14px; font-family: var(--kp-mono); font-weight: 700; background: #f8fafc;">
            <button type="button" onclick="copyText('inputMerchantCode', 'Kode Merchant')" class="kp-btn kp-btn-outline">
                <i class="fa-solid fa-copy"></i> Salin
            </button>
        </div>
    </div>

    <!-- API Key -->
    <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 4px;">API Key (Kunci Publik)</label>
        <div style="display: flex; gap: 8px;">
            <input type="text" readonly value="{{ $merchant->api_key }}" id="inputApiKey"
                   style="flex: 1; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); background: #f8fafc;">
            <button type="button" onclick="copyText('inputApiKey', 'API Key')" class="kp-btn kp-btn-outline">
                <i class="fa-solid fa-copy"></i> Salin
            </button>
        </div>
    </div>

    <!-- Secret Key -->
    <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 4px;">Secret Key (Kunci Rahasia)</label>
        <div style="display: flex; gap: 8px;">
            <input type="password" readonly value="{{ $merchant->secret_key }}" id="inputSecretKey"
                   style="flex: 1; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); background: #f8fafc;">
            <button type="button" onclick="toggleSecret()" class="kp-btn kp-btn-outline" id="btnToggleSecret">
                <i class="fa-solid fa-eye"></i>
            </button>
            <button type="button" onclick="copyText('inputSecretKey', 'Secret Key')" class="kp-btn kp-btn-outline">
                <i class="fa-solid fa-copy"></i> Salin
            </button>
        </div>
    </div>

    <!-- Webhook URL Form -->
    <form action="{{ route('merchant.webhook.update') }}" method="POST">
        @csrf
        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--kp-text-muted); margin-bottom: 4px;">
            URL Webhook Callback (Tujuan Notifikasi Pembayaran Lunas)
        </label>
        <div style="display: flex; gap: 8px;">
            <input type="url" name="webhook_url" value="{{ old('webhook_url', $merchant->webhook_url) }}" 
                   placeholder="https://mikhmon.domainanda.com/callback/kurtech-pay" 
                   style="flex: 1; padding: 10px 14px; border-radius: 12px; border: 1.5px solid var(--kp-border); font-size: 13.5px; font-family: var(--kp-mono); outline: none;">
            <button type="submit" class="kp-btn kp-btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
        </div>
        <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 5px;">
            Setiap ada pelanggan yang bayar voucher, sistem Kurtech Pay akan otomatis mengirimkan sinyal HTTP POST ke URL ini agar voucher langsung aktif seketika.
        </div>
    </form>
</div>

<!-- Integration Guide & Code Sample -->
<div class="kp-card">
    <div class="kp-card-header">
        <div class="kp-card-title">
            <i class="fa-solid fa-book-open" style="color: #10b981;"></i> Contoh Integrasi API (PHP / cURL)
        </div>
    </div>

    <p style="font-size: 13px; color: var(--kp-text-muted); margin-bottom: 12px;">
        Gunakan endpoint <code>{{ url('/api/v1/transaction/create') }}</code> untuk membuat transaksi baru dari Mikhmon atau aplikasi Anda:
    </p>

    <pre style="background: #0f172a; color: #f8fafc; border-radius: 14px; padding: 16px; font-family: var(--kp-mono); font-size: 12px; line-height: 1.6; overflow-x: auto;">
$url = "{{ url('/api/v1/transaction/create') }}";

$payload = [
    'api_key'        => '{{ $merchant->api_key }}',
    'amount'         => 5000,
    'order_id'       => 'VCR-' . time(),
    'service_name'   => 'Voucher WiFi 3 Jam',
    'customer_name'  => 'Pelanggan Hotspot',
    'callback_url'   => '{{ $merchant->webhook_url ?: "https://mikhmon.anda.com/callback" }}'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

// Ambil Link Pembayaran untuk ditampilkan ke pembeli:
$checkoutUrl = $response['data']['checkout_url'];
header("Location: " . $checkoutUrl);
</pre>
</div>
@endsection

@section('scripts')
<script>
    function copyText(elemId, label) {
        const input = document.getElementById(elemId);
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            alert(label + ' berhasil disalin ke clipboard!');
        });
    }

    function toggleSecret() {
        const input = document.getElementById('inputSecretKey');
        const btn = document.getElementById('btnToggleSecret');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i class="fa-solid fa-eye"></i>';
        }
    }
</script>
@endsection
