<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$greeting = "Halo Kak 👋\nSelamat datang di layanan *HotspotPro Kurtech* ✨\n\nSilakan balas dengan angka menu di bawah ini:\n1️⃣ *Daftar Paket* (Order Produk)\n2️⃣ *Hubungi Admin*\n\n_Ketik angka 1 atau 2 lalu kirim_";
$dana = "💳 *Pembayaran via DANA*\n\nSilakan transfer sejumlah:\nTotal: *Rp [amount]*\n\nKe nomor DANA berikut:\n📌 *[dana_number]*\n\n_Kirimkan foto/screenshot bukti transfer Anda ke chat ini agar sistem dapat segera memproses kode Voucher Anda._ ⏳";
$bca = "🏦 *Pembayaran via [bank_name]*\n\nSilakan transfer sejumlah:\nTotal: *Rp [amount]*\n\nKe rekening [bank_name] berikut:\n📌 *[bank_number]*\n👤 A/N *[bank_holder]*\n\n_Kirimkan foto/screenshot bukti transfer Anda ke chat ini agar sistem dapat segera memproses kode Voucher Anda._ ⏳";
$success = "✅ *PEMBAYARAN BERHASIL*\n\nTerima kasih, pembelian paket *[package_name]* Anda telah aktif!\n\nBerikut akses WiFi Anda:\n👤 Username: *[username]*\n🔑 Password: *[password]*\n\n_Gunakan detail di atas pada halaman login WiFi kami._\nSelamat berselancar! 🚀\n\n(Ketik *Menu* jika ingin memesan lagi di lain waktu)";

App\Models\Setting::set('wa_greeting', $greeting);
App\Models\Setting::set('wa_dana_instructions', $dana);
App\Models\Setting::set('wa_bca_instructions', $bca);
App\Models\Setting::set('wa_success_message', $success);
echo "Templates updated successfully!";
