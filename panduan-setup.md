# Panduan Setup Hotspot Backend (Laravel)

Sistem backend hotspot Anda telah selesai dibuat struktur utamanya menggunakan **Laravel 11**.
Panduan ini dirancang untuk Anda dan klien Anda saat ingin meng-online-kan sistem ini baik di **XAMPP (Windows)** maupun **Proxmox (Ubuntu)**.

## 1. Instalasi Pertama Kali (XAMPP / Ubuntu)

Pastikan Anda memiliki PHP (Minimal 8.2), MySQL/MariaDB, dan Composer.
*Jika menggunakan XAMPP versi terbaru, PHP dan MySQL sudah siap pakai.*

1. **Buka Terminal / Command Prompt**, masuk ke folder instalasi ini (contoh: `htdocs/hotspot-backend`).
2. Jalankan perintah untuk menginstal dependensi:
   ```bash
   composer install
   ```
3. Buat database kosong di MySQL/phpMyAdmin dengan nama (misal) `hotspot_db`.
4. Sesuaikan konfigurasi di file `.env` terutama bagian database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hotspot_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Isi juga konfigurasi MikroTik dan Payment Gateway di file `.env` yang sama.
6. Jalankan Migrasi Database untuk membuat tabel secara otomatis:
   ```bash
   php artisan migrate
   ```

## 2. Setup MikroTik Walled Garden

Agar pelanggan yang **belum memiliki voucher** (belum login) tetap bisa membuka halaman pembayaran (Tripay/Xendit) dan website Anda, IP/Domain gateway tersebut harus di-bypass menggunakan *Walled Garden*.

Buka **Terminal MikroTik / Winbox** lalu copy-paste perintah berikut:

```routeros
/ip hotspot walled-garden
add dst-host=*.tripay.co.id comment="Tripay Gateway"
add dst-host=*.xendit.co comment="Xendit Gateway"
add dst-host=api.tripay.co.id
add dst-host=tripay.co.id

# Ganti dengan domain / IP backend Laravel Anda sendiri
add dst-host=domain-anda.com comment="Backend Hotspot"
```

Jika Payment Gateway menggunakan IP address untuk sandbox/production, Anda juga bisa menambahkannya di `walled-garden ip`:
```routeros
/ip hotspot walled-garden ip
add action=accept dst-address=104.18.0.0/16 comment="Cloudflare IPs Tripay"
```

## 3. Snippet HTML Halaman Login Hotspot

Di MikroTik Anda, edit file `login.html`. Tambahkan tombol **Beli Voucher** yang mengarah ke website Laravel Anda.

```html
<!-- Tambahkan ini di dalam login.html MikroTik -->
<div style="margin-top: 20px; text-align: center;">
    <p>Belum punya voucher?</p>
    <a href="http://domain-anda.com/checkout" 
       style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
       Beli Voucher Online
    </a>
</div>
```

## 4. Penjelasan Cara Kerja Integrasi

1. Pelanggan connect ke WiFi Hotspot, diarahkan ke `login.html` MikroTik.
2. Pelanggan klik "Beli Voucher Online", dialihkan ke Web Laravel.
3. Pelanggan memilih paket dan mengisi data diri, Web Laravel memanggil `CheckoutController` (mengembalikan Virtual Account/QRIS Tripay).
4. Pelanggan membayar via M-Banking.
5. Tripay mengirim callback (Webhook) ke `http://domain-anda.com/api/webhook/tripay`.
6. `WebhookController` di Laravel memvalidasi pembayaran dan memanggil `MikrotikService`.
7. `MikrotikService` otomatis meng-inject username/password ke MikroTik via API.
8. Akun siap digunakan untuk login.
