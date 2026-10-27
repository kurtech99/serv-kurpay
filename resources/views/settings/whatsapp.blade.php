@extends('layouts.app')

@section('page_title', 'Pengaturan Bot WhatsApp')

@section('content')
<div class="glass-card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0; color: var(--text-color);">Koneksi WhatsApp</h2>
            <p style="margin: 5px 0 0 0; color: var(--text-muted);">Hubungkan nomor WhatsApp Anda sebagai server Bot.</p>
        </div>
        <div id="connectionStatus">
            @if($status === 'CONNECTED')
                <span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 6px 16px; border-radius: 20px; font-weight: bold;">
                    <i class="fa-solid fa-circle-check"></i> TERHUBUNG
                </span>
            @else
                <span style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 6px 16px; border-radius: 20px; font-weight: bold;">
                    <i class="fa-solid fa-circle-xmark"></i> TERPUTUS
                </span>
            @endif
        </div>
    </div>

    <!-- QR Section -->
    <div id="qrSection" style="margin-top: 24px; text-align: center; display: {{ $status === 'CONNECTED' ? 'none' : 'block' }};">
        <p style="color: var(--text-color); margin-bottom: 15px;">Silakan scan QR Code di bawah menggunakan WhatsApp HP Anda (Tautkan Perangkat):</p>
        <div id="qrCodeContainer" style="background: white; display: inline-block; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color);">
            @if($qrCode)
                <img id="qrImage" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrCode) }}" alt="Scan QR Code" style="width: 250px; height: 250px;">
            @else
                <div id="qrPlaceholder" style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center; color: #7f8c8d; font-size: 0.9rem;">
                    <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Menunggu QR dari Bot WA...
                </div>
            @endif
        </div>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 10px;">Halaman ini otomatis me-refresh QR Code setiap 5 detik.</p>
    </div>

    <div id="connectedSection" style="margin-top: 24px; text-align: center; display: {{ $status === 'CONNECTED' ? 'block' : 'none' }};">
        <div style="font-size: 3rem; color: #2ecc71; margin-bottom: 10px;">
            <i class="fa-solid fa-robot"></i>
        </div>
        <h4 style="color: var(--text-color); margin-top: 0; margin-bottom: 5px;">Bot Anda Sedang Aktif!</h4>
        <p style="color: var(--text-muted); margin-bottom: 15px;">Bot siap membalas otomatis & mencetak voucher hotspot pelanggan.</p>
        
        <form action="{{ route('settings.whatsapp.logout') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi WhatsApp?')">
            @csrf
            <button type="submit" class="btn" style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-right-from-bracket"></i> Putuskan Hubungan (Logout)
            </button>
        </form>
    </div>
</div>

<div class="glass-card">
    <h2 style="margin-top: 0; margin-bottom: 24px; color: var(--text-color);">Template Balasan Chat</h2>

    @if(session('success'))
        <div style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(46, 204, 113, 0.3);">
            <i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.whatsapp.update') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 24px; padding: 16px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 4px 0; color: var(--brand-primary); font-size: 16px;">Layanan WiFi Rumah</h3>
                <p style="margin: 0; color: var(--text-muted); font-size: 13px;">Tampilkan menu WiFi Rumah di balasan otomatis WhatsApp.</p>
            </div>
            <label style="position: relative; display: inline-block; width: 50px; height: 26px;">
                <input type="checkbox" name="wa_home_wifi_enabled" value="1" {{ $homeWifiEnabled == '1' ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ $homeWifiEnabled == '1' ? 'var(--brand-primary)' : 'rgba(255,255,255,0.2)' }}; transition: .4s; border-radius: 34px; border: 2px solid {{ $homeWifiEnabled == '1' ? 'var(--brand-primary)' : 'var(--border-color)' }};">
                    <span style="position: absolute; content: ''; height: 18px; width: 18px; left: {{ $homeWifiEnabled == '1' ? '26px' : '2px' }}; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                </span>
            </label>
            <script>
                // Simple script to toggle visual appearance immediately
                document.querySelector('input[name="wa_home_wifi_enabled"]').addEventListener('change', function() {
                    const spanBg = this.nextElementSibling;
                    const spanCircle = spanBg.querySelector('span');
                    if(this.checked) {
                        spanBg.style.backgroundColor = 'var(--brand-primary)';
                        spanBg.style.borderColor = 'var(--brand-primary)';
                        spanCircle.style.left = '26px';
                    } else {
                        spanBg.style.backgroundColor = 'rgba(255,255,255,0.2)';
                        spanBg.style.borderColor = 'var(--border-color)';
                        spanCircle.style.left = '2px';
                    }
                });
            </script>
        </div>

        <!-- Welcome Message -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 8px;">Teks Pembuka Pesan Sambutan</label>
            <textarea name="wa_greeting" style="width: 100%; height: 80px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.9rem;" required>{{ $greeting }}</textarea>
            <small style="color: var(--text-muted);">Hanya isi dengan kalimat sapaan (contoh: "Halo Kak!"). Daftar menu angka 1, 2, 3 akan ditambahkan <b>secara otomatis</b> oleh sistem di bawah pesan ini.</small>
        </div>

        <!-- Home Package Reply Message -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 8px;">Pesan Balasan Pendaftaran Pasang WiFi Rumah</label>
            <textarea name="wa_home_reply" style="width: 100%; height: 120px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.9rem;" required>{{ $homeReply ?? "Terima kasih! Anda memilih pasang WiFi paket *[package_name]*.\n\nUntuk memproses pendaftaran dan penjadwalan survei, silakan lanjutkan chat dengan Teknisi/Admin kami melalui link berikut:\n\n👉 https://wa.me/6283826164767?text=Halo%20Admin,%20saya%20mau%20pasang%20WiFi%20paket%20[package_name_url]\n\nSilakan klik link di atas ya Kak!" }}</textarea>
            <small style="color: var(--text-muted);">Gunakan tag <code style="color: var(--primary-color);">[package_name]</code> untuk teks biasa, dan <code style="color: var(--primary-color);">[package_name_url]</code> jika diletakkan di dalam link URL <code style="color: var(--primary-color);">?text=...</code> agar spasi tidak rusak.</small>
        </div>

        <!-- DANA Instructions -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 8px;">Instruksi Pembayaran DANA</label>
            <textarea name="wa_dana_instructions" style="width: 100%; height: 120px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.9rem;" required>{{ $danaInstructions }}</textarea>
            <small style="color: var(--text-muted);">Gunakan tag <code style="color: var(--primary-color);">[amount]</code> untuk memunculkan harga paket otomatis.</small>
        </div>

        <!-- BCA Instructions -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 8px;">Instruksi Pembayaran BCA</label>
            <textarea name="wa_bca_instructions" style="width: 100%; height: 120px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.9rem;" required>{{ $bcaInstructions }}</textarea>
            <small style="color: var(--text-muted);">Gunakan tag <code style="color: var(--primary-color);">[amount]</code> untuk memunculkan harga paket otomatis.</small>
        </div>

        <!-- Success Message -->
        <div style="margin-bottom: 24px;">
            <label style="display: block; color: var(--text-color); font-weight: bold; margin-bottom: 8px;">Pesan Voucher Sukses Kirim</label>
            <textarea name="wa_success_message" style="width: 100%; height: 150px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-color); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.9rem;" required>{{ $successMessage }}</textarea>
            <small style="color: var(--text-muted);">Gunakan tag wajib: <code style="color: var(--primary-color);">[package_name]</code>, <code style="color: var(--primary-color);">[username]</code>, dan <code style="color: var(--primary-color);">[password]</code>.</small>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border-radius: 8px; font-weight: bold;">
            <i class="fa-solid fa-save"></i> Simpan Pengaturan
        </button>
    </form>
</div>

<!-- AJAX Polling untuk QR & Connection Status -->
<script>
    let currentQr = '{{ $qrCode }}';
    
    setInterval(function() {
        fetch('{{ route('settings.whatsapp') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update Status Badge
            const statusContainer = document.getElementById('connectionStatus');
            const qrSection = document.getElementById('qrSection');
            const connectedSection = document.getElementById('connectedSection');
            
            if (data.status === 'CONNECTED') {
                statusContainer.innerHTML = `<span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 6px 16px; border-radius: 20px; font-weight: bold;"><i class="fa-solid fa-circle-check"></i> TERHUBUNG</span>`;
                qrSection.style.display = 'none';
                connectedSection.style.display = 'block';
                currentQr = '';
            } else {
                statusContainer.innerHTML = `<span style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 6px 16px; border-radius: 20px; font-weight: bold;"><i class="fa-solid fa-circle-xmark"></i> TERPUTUS</span>`;
                qrSection.style.display = 'block';
                connectedSection.style.display = 'none';
                
                // Update QR Code HANYA jika QR-nya berubah
                const qrContainer = document.getElementById('qrCodeContainer');
                if (data.qrCode) {
                    if (data.qrCode !== currentQr) {
                        currentQr = data.qrCode;
                        qrContainer.innerHTML = `<img id="qrImage" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(data.qrCode)}" alt="Scan QR Code" style="width: 250px; height: 250px;">`;
                    }
                } else {
                    if (currentQr !== '') {
                        currentQr = '';
                        qrContainer.innerHTML = `<div id="qrPlaceholder" style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center; color: #7f8c8d; font-size: 0.9rem;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Menunggu QR dari Bot WA...</div>`;
                    }
                }
            }
        })
        .catch(err => console.log('Error polling status WA:', err));
    }, 5000);
</script>
@endsection
