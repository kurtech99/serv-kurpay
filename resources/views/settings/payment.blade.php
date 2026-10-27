@extends('layouts.app')

@section('page_title', 'Pengaturan Payment Gateway')

@section('content')
<div class="glass-card">
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0; color: var(--text-color);">Integrasi Payment Gateway</h2>
        <p style="margin: 5px 0 0 0; color: var(--text-muted);">Pilih dan atur kredensial pembayaran otomatis QRIS Anda.</p>
    </div>

    @if(session('success'))
        <div style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(46, 204, 113, 0.3);">
            <i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.payment.update') }}" method="POST">
        @csrf

        <!-- Manual Payments Config -->
        <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 12px;">Metode Pembayaran Manual (WhatsApp Chat)</label>
            
            <!-- DANA Config -->
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-color); cursor: pointer; margin-bottom: 10px;">
                    <input type="checkbox" name="payment_manual_dana_active" value="1" {{ $danaActive == '1' ? 'checked' : '' }}>
                    <span>Aktifkan Pembayaran Manual via <strong>DANA</strong></span>
                </label>
                <div style="margin-top: 10px; display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Nomor DANA / Telepon</label>
                        <input type="text" name="payment_manual_dana_number" value="{{ $danaNumber }}" style="width: 100%; padding: 8px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
                    </div>
                </div>
            </div>

            <!-- Bank Manual Config -->
            <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-color); cursor: pointer; margin-bottom: 10px;">
                    <input type="checkbox" name="payment_manual_bca_active" value="1" {{ $bcaActive == '1' ? 'checked' : '' }}>
                    <span>Aktifkan Pembayaran Manual via <strong>Transfer Bank</strong></span>
                </label>
                <div style="margin-top: 10px; display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Nama Bank (cth: BCA, Mandiri)</label>
                        <input type="text" name="payment_manual_bank_brand" value="{{ $bankBrand }}" style="width: 100%; padding: 8px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Nomor Rekening</label>
                        <input type="text" name="payment_manual_bank_number" value="{{ $bankNumber }}" style="width: 100%; padding: 8px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Nama Pemilik Rekening (A/N)</label>
                        <input type="text" name="payment_manual_bank_name" value="{{ $bankName }}" style="width: 100%; padding: 8px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Gateway Selector -->
        <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 10px;">Payment Gateway Aktif</label>
            <div style="display: flex; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-color); cursor: pointer;">
                    <input type="radio" name="payment_gateway_active" value="tripay" {{ $active === 'tripay' ? 'checked' : '' }} onclick="toggleGatewaySection('tripay')">
                    <strong>Tripay (Rekomendasi)</strong>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-color); cursor: pointer;">
                    <input type="radio" name="payment_gateway_active" value="midtrans" {{ $active === 'midtrans' ? 'checked' : '' }} onclick="toggleGatewaySection('midtrans')">
                    <strong>Midtrans</strong>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; color: var(--text-color); cursor: pointer;">
                    <input type="radio" name="payment_gateway_active" value="xendit" {{ $active === 'xendit' ? 'checked' : '' }} onclick="toggleGatewaySection('xendit')">
                    <strong>Xendit</strong>
                </label>
            </div>
        </div>

        <!-- Tripay Credentials -->
        <div id="tripaySection" class="gateway-fields" style="display: {{ $active === 'tripay' ? 'block' : 'none' }}; margin-bottom: 20px;">
            <h4 style="color: var(--primary-color); margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-credit-card"></i> Kredensial Tripay (Sandbox / Production)</h4>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">Merchant Code</label>
                <input type="text" name="tripay_merchant_code" value="{{ $tripayMerchantCode }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">API Key</label>
                <input type="password" name="tripay_api_key" value="{{ $tripayApiKey }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">Private Key</label>
                <input type="password" name="tripay_private_key" value="{{ $tripayPrivateKey }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
        </div>

        <!-- Midtrans Credentials -->
        <div id="midtransSection" class="gateway-fields" style="display: {{ $active === 'midtrans' ? 'block' : 'none' }}; margin-bottom: 20px;">
            <h4 style="color: var(--primary-color); margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-credit-card"></i> Kredensial Midtrans</h4>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">Server Key</label>
                <input type="password" name="midtrans_server_key" value="{{ $midtransServerKey }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">Client Key</label>
                <input type="text" name="midtrans_client_key" value="{{ $midtransClientKey }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
        </div>

        <!-- Xendit Credentials -->
        <div id="xenditSection" class="gateway-fields" style="display: {{ $active === 'xendit' ? 'block' : 'none' }}; margin-bottom: 20px;">
            <h4 style="color: var(--primary-color); margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-credit-card"></i> Kredensial Xendit</h4>
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-color); margin-bottom: 5px; font-size: 0.9rem;">Secret API Key</label>
                <input type="password" name="xendit_api_key" value="{{ $xenditApiKey }}" style="width: 100%; padding: 10px; border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color);">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 15px;">
            <i class="fa-solid fa-save"></i> Simpan Kredensial
        </button>
    </form>
</div>

<script>
function toggleGatewaySection(active) {
    document.querySelectorAll('.gateway-fields').forEach(el => {
        el.style.display = 'none';
    });
    document.getElementById(active + 'Section').style.display = 'block';
}
</script>
@endsection
