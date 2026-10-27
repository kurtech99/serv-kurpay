<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS #{{ $trx->trx_id }} - {{ $trx->merchant->business_name ?? 'Kurtech Pay' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="{{ asset('js/qrious.min.js') }}"></script>
    
    <style>
        :root {
            --kp-primary: #2563eb;
            --kp-primary-dark: #1d4ed8;
            --kp-success: #10b981;
            --kp-success-dark: #059669;
            --kp-danger: #ef4444;
            --kp-bg: #f1f5f9;
            --kp-card-bg: #ffffff;
            --kp-text: #0f172a;
            --kp-text-muted: #64748b;
            --kp-border: #e2e8f0;
            --kp-font: 'Plus Jakarta Sans', sans-serif;
            --kp-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--kp-font);
            background-color: var(--kp-bg);
            color: var(--kp-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .pay-card {
            background: var(--kp-card-bg);
            width: 100%;
            max-width: 440px;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.12), 0 0 1px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1.5px solid var(--kp-border);
            text-align: center;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pay-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 22px 20px 18px;
            position: relative;
        }

        .merchant-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #93c5fd;
            margin-bottom: 8px;
        }

        .business-title {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .order-desc {
            font-size: 12.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .pay-body {
            padding: 22px 20px 24px;
        }

        .qr-wrapper {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            padding: 14px;
            display: inline-block;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            margin-bottom: 16px;
            position: relative;
        }

        .qr-image {
            width: 240px;
            height: 240px;
            object-fit: contain;
            display: block;
            border-radius: 8px;
        }

        .qr-brand-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #e2e8f0;
        }

        .amount-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .amount-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--kp-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .amount-display {
            font-size: 26px;
            font-family: var(--kp-mono);
            font-weight: 900;
            color: var(--kp-primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .copy-btn {
            background: #eff6ff;
            color: var(--kp-primary);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: var(--kp-font);
        }

        .copy-btn:hover {
            background: var(--kp-primary);
            color: #ffffff;
        }

        .amount-warning {
            font-size: 11.5px;
            color: #b91c1c;
            font-weight: 700;
            margin-top: 6px;
            background: #fef2f2;
            border-radius: 6px;
            padding: 4px 8px;
            display: inline-block;
        }

        .timer-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #d97706;
            margin-bottom: 18px;
            background: #fffbeb;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #fef3c7;
        }

        .supported-apps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 11px;
            color: var(--kp-text-muted);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .app-pill {
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        .btn-check {
            width: 100%;
            padding: 13px;
            background: var(--kp-primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 14.5px;
            font-weight: 800;
            font-family: var(--kp-font);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
        }

        .btn-check:hover {
            background: var(--kp-primary-dark);
            transform: translateY(-1px);
        }

        /* Test simulation button for testing */
        .btn-sim {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: #f0fdf4;
            color: #166534;
            border: 1.5px dashed #86efac;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-sim:hover {
            background: #dcfce7;
        }

        /* Success screen overlay */
        .success-overlay {
            display: none;
            padding: 36px 20px;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }

        .success-icon {
            width: 72px;
            height: 72px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin: 0 auto 16px;
            box-shadow: 0 10px 20px rgba(22,163,74,0.2);
        }
    </style>
</head>
<body>

<div class="pay-card">
    <div class="pay-header">
        <div class="merchant-badge">
            <i class="fa-solid fa-wifi"></i> {{ $trx->merchant->business_name ?? 'Kurtech Pay' }}
        </div>
        <div class="business-title">{{ $merchantName }}</div>
        <div class="order-desc">ID Transaksi: #{{ $trx->trx_id }}</div>
    </div>

    <!-- Active Payment State -->
    <div class="pay-body" id="paymentActiveView">
        <div class="timer-box">
            <i class="fa-regular fa-clock"></i>
            <span>Selesaikan pembayaran dalam: <b id="countdownTimer" style="font-family: var(--kp-mono);">14:59</b></span>
        </div>

        <div class="qr-wrapper">
            <!-- Official Red QRIS Top Header -->
            <div style="background: #be123c; color: white; border-radius: 8px 8px 0 0; padding: 6px 12px; margin: -14px -14px 10px -14px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 11px; font-weight: 900; letter-spacing: 1px;">QRIS</span>
                <span style="font-size: 9.5px; font-weight: 700; opacity: 0.9; text-transform: uppercase;">Pembayaran Nasional</span>
            </div>

            <!-- Dynamic QR Canvas (Auto-Nominal Lock) -->
            <canvas id="qrCodeCanvas" class="qr-image" style="width: 240px; height: 240px; margin: 0 auto; display: block; border-radius: 6px;"></canvas>

            <!-- Fallback Image if JS Canvas is disabled -->
            <img id="qrCodeFallback" src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode($qrString) }}" alt="QRIS Standar Indonesia" class="qr-image" style="display: none; width: 240px; height: 240px; margin: 0 auto; border-radius: 6px;">

            <div style="background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 800; border-radius: 20px; padding: 4px 10px; margin-top: 10px; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-bolt" style="color: #059669;"></i> QRIS Dinamis Otomatis Nominal
            </div>

            <div class="qr-brand-bar">
                <span>NMID: {{ $nmid }} &bull; {{ $merchantName }}</span>
            </div>
        </div>

        <div class="amount-box">
            <div class="amount-title">Total Nominal Yang Harus Ditransfer:</div>
            <div class="amount-display">
                <span id="amountText">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                <button type="button" class="copy-btn" onclick="copyAmount()" title="Salin nominal">
                    <i class="fa-regular fa-copy"></i> Salin
                </button>
            </div>
            <div style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 8px; padding: 7px 10px; font-size: 11.5px; font-weight: 600; margin-top: 8px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-solid fa-circle-check" style="color: #2563eb;"></i>
                <span><b>Nominal Otomatis!</b> Di scan langsung terisi <b>Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</b> di DANA/BCA/m-Banking.</span>
            </div>
            @if($trx->customer_fee > 0)
                <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 8px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                    <span>Harga: <b>Rp {{ number_format($trx->amount, 0, ',', '.') }}</b> + Biaya: <b>Rp {{ number_format($trx->customer_fee, 0, ',', '.') }}</b></span>
                    <span>Kode Unik: <b>+{{ $trx->unique_code }}</b></span>
                </div>
            @endif
        </div>

        <div class="supported-apps">
            <span>Bisa dibayar dengan:</span>
            <span class="app-pill">DANA</span>
            <span class="app-pill">BCA</span>
            <span class="app-pill">BRI</span>
            <span class="app-pill">Mandiri</span>
            <span class="app-pill">GoPay</span>
            <span class="app-pill">OVO</span>
            <span class="app-pill">ShopeePay</span>
            <span class="app-pill">Semua Bank</span>
        </div>

        <button type="button" class="btn-check" onclick="checkStatusManual()">
            <i class="fa-solid fa-arrows-rotate" id="checkIcon"></i> Cek Status Pembayaran
        </button>

        <!-- Live Simulation Button for testing -->
        <form action="{{ route('dana.callback') }}" method="POST" style="margin-top: 10px;" onsubmit="return confirm('Tes simulasi pembayaran sukses tanpa transfer uang asli?')">
            @csrf
            <input type="hidden" name="nominal" value="{{ (int)$trx->total_amount }}">
            <input type="hidden" name="secret" value="kurtech_dana_2026">
            <input type="hidden" name="trx_id" value="{{ $trx->trx_id }}">
            <button type="submit" class="btn-sim">
                <i class="fa-solid fa-vial-circle-check"></i> [Mode Tes] Klik Di Sini Untuk Tes Lunas
            </button>
        </form>
    </div>

    <!-- Paid / Success State -->
    <div class="success-overlay" id="paymentSuccessView">
        <div class="success-icon">
            <i class="fa-solid fa-check"></i>
        </div>
        <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Pembayaran Berhasil!</h3>
        <p style="font-size: 13px; color: var(--kp-text-muted); margin-bottom: 20px;">
            Terima kasih! Pembayaran sebesar <b>Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</b> telah kami terima.
        </p>

        @if(!empty($trx->callback_url))
            <a href="{{ $trx->callback_url }}" id="redirectLink" style="display: inline-block; width: 100%; padding: 13px; background: var(--kp-success); color: white; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 14px;">
                <i class="fa-solid fa-arrow-right"></i> Kembali ke Voucher / Internet
            </a>
            <div style="font-size: 11.5px; color: var(--kp-text-muted); margin-top: 8px;">
                Mengarahkan otomatis dalam <b id="redirectTimer">3</b> detik...
            </div>
        @else
            <div style="background: #f1f5f9; padding: 12px; border-radius: 12px; font-size: 12.5px; color: #475569;">
                Voucher Hotspot Anda telah aktif. Silakan hubungkan kembali ke jaringan WiFi.
            </div>
        @endif
    </div>
</div>

<script>
    const trxId = "{{ $trx->trx_id }}";
    const statusUrl = "{{ route('pay.status', $trx->trx_id) }}";
    const rawAmount = "{{ (int) $trx->total_amount }}";
    const isAlreadyPaid = {{ $trx->isPaid() ? 'true' : 'false' }};
    const callbackUrl = "{{ $trx->callback_url }}";
    const qrString = @json($qrString);

    // Render Dynamic QRIS Canvas
    try {
        if (typeof QRious !== 'undefined' && qrString) {
            new QRious({
                element: document.getElementById('qrCodeCanvas'),
                value: qrString,
                size: 320,
                level: 'M'
            });
        } else {
            document.getElementById('qrCodeCanvas').style.display = 'none';
            document.getElementById('qrCodeFallback').style.display = 'block';
        }
    } catch (e) {
        console.error('QRious render error:', e);
        document.getElementById('qrCodeCanvas').style.display = 'none';
        document.getElementById('qrCodeFallback').style.display = 'block';
    }

    // Copy Amount
    function copyAmount() {
        navigator.clipboard.writeText(rawAmount).then(() => {
            alert('Nominal Rp ' + parseInt(rawAmount).toLocaleString('id-ID') + ' disalin!');
        });
    }

    // Timer Countdown (15 minutes)
    let timeLeft = 15 * 60;
    const timerElem = document.getElementById('countdownTimer');
    const timerInterval = setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerElem.innerText = 'Kadaluarsa';
            return;
        }
        const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const s = (timeLeft % 60).toString().padStart(2, '0');
        timerElem.innerText = `${m}:${s}`;
    }, 1000);

    // Show Success UI
    function showSuccess() {
        clearInterval(timerInterval);
        document.getElementById('paymentActiveView').style.display = 'none';
        document.getElementById('paymentSuccessView').style.display = 'block';

        if (callbackUrl) {
            let rTime = 3;
            const rElem = document.getElementById('redirectTimer');
            const rInterval = setInterval(() => {
                rTime--;
                if (rElem) rElem.innerText = rTime;
                if (rTime <= 0) {
                    clearInterval(rInterval);
                    window.location.href = callbackUrl;
                }
            }, 1000);
        }
    }

    // Check status via JSON polling
    function checkStatusManual() {
        const icon = document.getElementById('checkIcon');
        icon.classList.add('fa-spin');

        fetch(statusUrl)
            .then(res => res.json())
            .then(data => {
                icon.classList.remove('fa-spin');
                if (data.is_paid) {
                    showSuccess();
                } else {
                    alert('Pembayaran belum terdeteksi. Pastikan Anda sudah mentransfer nominal yang tepat!');
                }
            })
            .catch(() => {
                icon.classList.remove('fa-spin');
            });
    }

    // Auto-polling every 3.5 seconds
    if (!isAlreadyPaid) {
        const poller = setInterval(() => {
            fetch(statusUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.is_paid) {
                        clearInterval(poller);
                        showSuccess();
                    }
                })
                .catch(() => {});
        }, 3500);
    } else {
        showSuccess();
    }
</script>

</body>
</html>
