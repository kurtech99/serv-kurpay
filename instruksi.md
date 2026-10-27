# 📘 DOKUMENTASI LENGKAP & RUNBOOK SERVER KURTECH 2026
**File Tunggal / Master Command Center**: `instruksi.md`  
**Lokasi Root**: `G:\0. PROJEK KURTECH\2026\SERVER KURTECH\instruksi.md`  
**Projek**: Ekosistem Cloud SaaS Mikhmon, Billing WA Gateway, dan Portal VPN Multi-Port  
**Arsitektur Server**: Proxmox VE (Container CT 100, CT 101, CT 102, CT 103, MikroTik CHR)  
**Terakhir Diperbarui**: 26 Oktober 2026  
**Penyusun**: KURTECH Developer + Antigravity AI  

---

## 📑 DAFTAR ISI
1. [Prinsip Kerja & SOP Pengembangan (Local-First Development)](#1-prinsip-kerja--sop-pengembangan-local-first-development)
2. [Arsitektur Server & Ekosistem Jaringan](#2-arsitektur-server--ekosistem-jaringan)
3. [Rekap Seluruh Pembahasan & Masalah dari Awal](#3-rekap-seluruh-pembahasan--masalah-dari-awal)
   - [A. Log Hotspot & PPPoE Kosong/Tidak Akurat](#a-log-hotspot--pppoe-kosongtidak-akurat)
   - [B. Dual Billing Cycle (Siklus Pasang vs Tanggal Serentak)](#b-dual-billing-cycle-siklus-pasang-vs-tanggal-serentak)
   - [C. Sinkronisasi Tema & Perbaikan CSS Syntax Error](#c-sinkronisasi-tema--perbaikan-css-syntax-error)
   - [D. Kasus Tenant Mental & Offline (nusantara221, ainur, dila)](#d-kasus-tenant-mental--offline-nusantara221-ainur-dila)
   - [E. Penyederhanaan Sidebar Menu & Pembersihan Redundansi](#e-penyederhanaan-sidebar-menu--pembersihan-redundansi)
   - [F. Pemulihan Logo Asli Mikhmon & Favicon Domain](#f-pemulihan-logo-asli-mikhmon--favicon-domain)
   - [G. Normalisasi Akun rumah: Mikhmon Basic vs Billing Kurtech](#g-normalisasi-akun-rumah-mikhmon-basic-vs-billing-kurtech)
4. [Daftar Repositori & Pemetaan Kode Sumber](#4-daftar-repositori--pemetaan-kode-sumber)
5. [Tabel Komparasi Paket Layanan](#5-tabel-komparasi-paket-layanan)
6. [Panduan Eksekusi Deploy ke Server Proxmox](#6-panduan-eksekusi-deploy-ke-server-proxmox)
7. [Prinsip Keamanan & Anti-Overwrite Tenant](#7-prinsip-keamanan--anti-overwrite-tenant)
8. [Peta Rencana Pengembangan Masa Depan (Roadmap 2026+)](#8-peta-rencana-pengembangan-masa-depan-roadmap-2026)
   - [Modul 1: Komersialisasi TR-069 GenieACS](#modul-1-komersialisasi-tr-069-genieacs)
   - [Modul 2: Server FreeRADIUS Mandiri (Dual-Mode: API & RADIUS)](#modul-2-server-freeradius-mandiri-dual-mode-api--radius)
   - [Modul 3: Server DNS Cache Lokal (Perbedaan DNS vs Proxy)](#modul-3-server-dns-cache-lokal-perbedaan-dns-vs-proxy)
   - [Modul 4: Server CCTV Cloud Multi-Brand / Multi-Channel](#modul-4-server-cctv-cloud-multi-brand--multi-channel)
   - [Modul 5: Server Web Hosting aaPanel (1 Container untuk Puluhan Web)](#modul-5-server-web-hosting-aapanel-1-container-untuk-puluhan-web)
9. [SOP Penyesuaian Spek & Migrasi Hardware Server Proxmox](#9-sop-penyesuaian-spek--migrasi-hardware-server-proxmox)
   - [A. Penyesuaian Fleksibel RAM, CPU, dan Disk CT](#a-penyesuaian-fleksibel-ram-cpu-dan-disk-ct)
   - [B. 3 Metode Migrasi Hardware (Upgrade PC/Server Baru)](#b-3-metode-migrasi-hardware-upgrade-pcserver-baru)
   - [C. SOP Maintenance & Draf Pesan Siaran WhatsApp Resmi](#c-sop-maintenance--draf-pesan-siaran-whatsapp-resmi)

---

## ⚡ 1. PRINSIP KERJA & SOP PENGEMBANGAN (LOCAL-FIRST DEVELOPMENT)

> [!IMPORTANT]
> **Cara berpikir Anda 100% TEPAT dan merupakan standar terbaik industri (*Industry Best Practice*)!**  
> Sistem live produksi yang melayani pelanggan tidak boleh diuji coba secara langsung. Alur kerja yang benar adalah:

```
[ 1. Local PC Dev ]                 [ 2. Verifikasi ]                [ 3. Push GitHub ]            [ 4. Deploy Proxmox ]
Edit kode di folder lokal  ──>  Uji fitur & cek visual  ──>  Commit aman ke GitHub  ──>  Terapkan ke CT 101/102/103
(Aman, tidak ganggu live)        (Pastikan sudah puas)        (Riwayat kode tersimpan)       (Server langsung sinkron 1-klik)
```

### SOP Langkah Demi Langkah:
1. **Langkah 1 (Edit di Local PC)**: Seluruh perbaikan kode, UI, penambahan modul, maupun konfigurasi dilakukan di folder lokal komputer ini (`G:\0. PROJEK KURTECH\2026\SERVER KURTECH\`).
2. **Langkah 2 (Testing & Puas di Local)**: Jalankan testing lokal. Cek tampilan, respons database, dan koneksi. Jika masih kurang pas, revisi terus di lokal sampai benar-benar puas dan sempurna.
3. **Langkah 3 (Commit & Push ke GitHub)**: Setelah kode fix dan teruji, barulah kita simpan ke repositori GitHub (`kurtech99/mikhmon-billing` & `kurtech99/kurtech-vpn-portal`).
4. **Langkah 4 (Deploy Cepat ke Server Proxmox)**: Jalankan perintah curl patch di terminal Container Proxmox. Dalam hitungan 2 detik, server live akan langsung ter-update dengan kode yang sudah matang tanpa ada risiko error yang mengganggu pelanggan berjalan.

---

## 🌐 2. ARSITEKTUR SERVER & EKOSISTEM JARINGAN

| Container | Hostname / Layanan | IP Private | Domain Publik | Deskripsi Fungsi |
| :---: | :--- | :---: | :--- | :--- |
| **CT 100** | `Serv-Landing-Web` | `10.10.10.251` | `https://kurtech.my.id` | Landing page resmi, dokumentasi, dan Cloudflare Tunnel ingress. |
| **CT 101** | `Serv-Billing-Kurtech`| `10.10.10.250` | `https://billing.kurtech.my.id` | Engine Billing Kurtech Pro (Hotspot + PPPoE + WhatsApp Gateway + Keuangan). |
| **CT 102** | `Serv-VPN-Portal` | `10.10.10.249` | `https://remote.kurtech.my.id` | Portal VPN Remote Multi-Port (Laravel 11, User Dashboard, SuperAdmin, Tripay Payment Gateway, MikroTik CHR L2TP Auto-Provision). |
| **CT 103** | `Serv-Mikhmon` | `10.10.10.248` | `https://mikhmon.kurtech.my.id` | Mikhmon Cloud Standar Pure (Hotspot + PPPoE Mikhmon asli tanpa modul billing kompleks). |
| **Dev PC** | `Laptop Developer` | `10.10.10.253` | `http://10.10.10.253:8899` | Server penyedia patch tarball otomatis untuk rilis cepat ke seluruh CT. |
| **CHR** | `MikroTik VPN Server` | `10.10.10.1` | `id1.kurtech.my.id` / `vpn.kurtech.my.id` | Server tunneling VPN L2TP/IPSec & alokasi port forwarding router klien. |

---

## 🔍 3. REKAP SELURUH PEMBAHASAN & MASALAH DARI AWAL

### A. Log Hotspot & PPPoE Kosong/Tidak Akurat
- **Gejala**: Panel log hotspot di dashboard tidak menampilkan aktivitas user, dan log PPPoE hanya menampilkan koneksi aktif saat itu, bukan catatan riwayat (login, logout, gagal).
- **Penyebab Teknis**: RouterOS MikroTik secara default menonaktifkan topic `debug`. Query API lama yang menyertakan filter `?topics=hotspot,info,debug` ditolak oleh MikroTik sehingga mengembalikan hasil array kosong.
- **Solusi Diterapkan**:
  - Mengubah query MikroTik API menjadi `.proplist=.id,time,message,topics` yang bersifat universal.
  - Membangun regex parser di sisi PHP untuk mengelompokkan kategori event secara otomatis dengan penanda visual warna (Hijau = LOGIN, Merah = LOGOUT, Kuning = OTENTIKASI/GAGAL).
  - Mengaktifkan pembaruan real-time via AJAX polling di `dashboard/aload.php` dan `index.php`.

### B. Dual Billing Cycle (Siklus Pasang vs Tanggal Serentak)
- **Gejala**: Pengusaha ISP kesulitan menagih pelanggan yang mulai berlangganan di tanggal bervariasi (tengah bulan) jika sistem hanya mendukung tanggal isolir tetap (serentak).
- **Solusi Diterapkan**:
  - **Mode A (Anniversary / 30 Hari - Default)**: Tanggal jatuh tempo dihitung perorangan sesuai tanggal pasang pelanggan.
  - **Mode B (Fixed Date / Serentak)**: Semua pelanggan ditagih di tanggal yang sama (misal setiap tanggal 5).
  - **Grace Period (Toleransi Tunggakan)**: Fitur toleransi tenggang waktu (misal 3 hari setelah jatuh tempo) sebelum cron `auto_isolir.php` mengeksekusi profil isolir di MikroTik.
  - UI pengaturan interaktif di `billing/settings.php` lengkap dengan simulasi tanggal jatuh tempo dan tombol terapkan massal ke pelanggan lama.

### C. Sinkronisasi Tema & Perbaikan CSS Syntax Error
- **Gejala**: Pada tema terang (*Light Mode*), banyak teks dan card yang warnanya bertabrakan (teks putih di atas background putih, atau kotak gelap tertinggal di tema terang).
- **Penyebab Teknis**: Ditemukan syntax error di file `css/mikhmon-responsive.css` pada baris 3463 berupa kurung kurawal penutup `}` yang hilang. Akibatnya, browser mengabaikan lebih dari 700 baris style override tema di bawahnya.
- **Solusi Diterapkan**:
  - Memperbaiki penutupan blok CSS dan merapikan token variabel (`var(--bg-card)`, `var(--text-main)`, `var(--border-card)`).
  - Menghapus background hardcoded gelap di form pengaturan profil router.
  - Memastikan kontras teks selalu jelas di kedua mode (Light & Dark).

### D. Kasus Tenant Mental & Offline (`nusantara221`, `ainur`, `dila`)
- **Kasus nusantara221**:
  - *Mental Login*: File `admin.php` di CT 103 versi lama melakukan redirect ke `admin.php?id=sessions` yang memicu filter multi-tenant dan mengoper sesi ke portal utama.
  - *Status Offline*: Di MikroTik Winbox router klien, kredensial API adalah `nusantara221` / `nusantara223`, sedangkan di config Mikhmon masih tersimpan default `admin`.
  - *Solusi*: Sinkronisasi engine `admin.php` terpadu dengan cookie path `/`. Kredensial router diupdate ke `nusantara221` / `nusantara223` dan berhasil konek live (RB450Gx4, ROS 7.16.2).
- **Kasus ainur**: Mengatasi blank page login di CT 103 dengan menyamakan codebase multi-tenant URL pathing (`/ainur/?session=ainur`).
- **Kasus dila & arka-cijolang**: Mengatasi gagal ping socket router akibat rule rewrite `.htaccess` yang sempat menelan URL AJAX internal.

### E. Penyederhanaan Sidebar Menu & Pembersihan Redundansi
- **Gejala**: Menu samping (*Sidebar*) terlalu banyak kategori (6 kategori), nama menu menggunakan istilah birokratis yang panjang, dan ada fungsi ganda/dobel.
- **Solusi Diterapkan**:
  - Diringkas menjadi 4 kategori bersih dan logis:
    1. **MENU UTAMA**: Dashboard & Quick Overview.
    2. **BILLING & KEUANGAN**: Pelanggan, Tagihan & Pembayaran, Auto-Isolir, Laporan Keuangan, Pengaturan Tagihan.
    3. **JARINGAN & MIKROTIK**: Hotspot Voucher, Koneksi PPPoE, Monitor Sinyal/Interface, Log Aktivitas Router.
    4. **PENGATURAN & SISTEM**: Profil Router, Template Voucher, Admin & WhatsApp Gateway.
  - Menghilangkan menu duplikat dan menyederhanakan bahasa menjadi ramah operator.
  - Memperbaiki Highcharts dashboard agar dimuat langsung melalui path lokal `/js/highcharts/highcharts.js`.

### F. Pemulihan Logo Asli Mikhmon & Favicon Domain
- **Gejala**: Logo favicon berubah menjadi logo Kurtech di mana-mana, padahal Mikhmon Standar harus menggunakan identitas asli Mikhmon.
- **Solusi Diterapkan**:
  - `img/favicon.png` dikembalikan 100% ke logo asli Mikhmon (ikon titik-titik warna-warni kreasi Laksamadi Guko).
  - Di `include/headhtml.php`, favicon dimuat secara kondisional berdasarkan domain:
    - Jika diakses via `mikhmon.kurtech.my.id` ➔ Favicon memakai logo asli Mikhmon.
    - Jika diakses via `billing.kurtech.my.id` ➔ Favicon memakai logo resmi Kurtech.
  - Fitur cetak voucher (`voucher/print.php`) tetap fleksibel menggunakan logo kustom yang diupload pemilik router.

### G. Normalisasi Akun `rumah`: Mikhmon Basic vs Billing Kurtech
- **Gejala**: Saat akun `rumah` diaktifkan manual oleh SuperAdmin dengan paket *VPN + Mikhmon Basic (Hotspot + PPPoE - Rp 15.000 / bln)*, kartu router di dashboard klien malah memunculkan badge hijau `BILLING KURTECH` dan tombol `Open Billing`.
- **Akar Masalah**:
  1. Di `portal-vpn/app/Models/VpnAccount.php` baris 54, username `'rumah'` ter-hardcode di dalam array whitelist billing:
     ```php
     if (in_array(strtolower($this->username), ['arka-cibiuk', 'arka-cijolang', 'rumah', 'dila'])) {
         return true; // Paksa jadi billing apapun paketnya!
     }
     ```
  2. Pada controller aktivasi manual `SuperAdminVpnController.php`, prefix transaksi untuk *Mikhmon Lite* dan *Billing Pro* sama-sama menggunakan `VPN-MIKH-` dengan nominal Rp 0, sehingga sistem tidak dapat membedakan tingkatan paket.
  3. Di `cloud_saas/tenants/rumah/tenant_config.json`, tersimpan konfigurasi lama hasil testing Agustus dengan nilai `"plan_type": "mikhmon_wa_pro"`.
- **Solusi Diterapkan**:
  - Menghapus `'rumah'` dan `'dila'` dari seluruh baris hardcode.
  - Memisahkan prefix transaksi SuperAdmin secara eksplisit:
    - **Mikhmon Basic / Lite**: Prefix `VPN-LITE-` ➔ Mutlak Mikhmon Standar (`has_billing = false`).
    - **Billing Kurtech Pro**: Prefix `VPN-PRO-` ➔ Mengaktifkan Billing Kurtech (`has_billing = true`).
    - **GenieACS Enterprise**: Prefix `VPN-ACS-` ➔ Mengaktifkan Billing + NOC TR-069.
  - Mengupdate model `VpnAccount` untuk memeriksa `tenant_config.json` plan `mikhmon_lite` sebagai acuan utama (*authoritative source of truth*).
  - Hasil akhir akun `rumah`:
    - **Badge**: `MIKHMON STANDAR` (Biru)
    - **Tombol**: `Open Mikhmon`
    - **Target URL**: `https://mikhmon.kurtech.my.id/rumah/`

---

## 📁 4. DAFTAR REPOSITORI & PEMETAAN KODE SUMBER

| Repositori GitHub | Branch | Folder Lokal | Target Server |
| :--- | :---: | :--- | :--- |
| `kurtech99/mikhmon-billing` | `main` | `g:/0. PROJEK KURTECH/2026/SERVER KURTECH/billing-kurtech` | **CT 101** (`/var/www/html/`) & **CT 103** (`/var/www/html/`) |
| `kurtech99/kurtech-vpn-portal` | `main` | `g:/0. PROJEK KURTECH/2026/SERVER KURTECH/portal-vpn` | **CT 102** (`/var/www/html/vpnremote/`) |

---

## 📊 5. TABEL KOMPARASI PAKET LAYANAN

| Parameter | 1. Mikhmon Basic / Lite | 2. Billing Kurtech Pro | 3. GenieACS Enterprise |
| :--- | :---: | :---: | :---: |
| **Tarif Bulanan** | Rp 15.000 / bln | Rp 45.000 / bln | Rp 100.000 / bln |
| **Prefix Transaksi** | `VPN-LITE-` | `VPN-PRO-` | `VPN-ACS-` |
| **Alokasi Port** | 4 Port (Winbox, API, Web, Custom) | 4 Port (Winbox, API, Web, Custom) | 4 Port (Winbox, API, Web, GenieACS) |
| **Subdomain Web** | `mikhmon.kurtech.my.id` | `billing.kurtech.my.id` | `billing.kurtech.my.id` + `acs.kurtech.my.id` |
| **Badge di Dashboard** | `MIKHMON STANDAR` (Biru) | `BILLING KURTECH` (Hijau) | `GENIEACS + BILLING` (Ungu) |
| **Tombol Launch** | `Open Mikhmon` | `Open Billing` | `Open Billing` + `NOC Panel` + `GenieACS UI` |
| **Manajemen Pelanggan** | Hotspot & PPPoE Saja | Hotspot, PPPoE, Invoice, Keuangan | Full Suite + Monitoring ONT Jarak Jauh |
| **WhatsApp Gateway** | Tidak Ada | Ada (Reminder & Notifikasi Otomatis) | Ada (Full Otomatis) |

---

## 🚀 6. PANDUAN EKSEKUSI DEPLOY KE SERVER PROXMOX

Jalankan perintah satu baris berikut di masing-masing Container via Proxmox Web Console / SSH:

### 1. Eksekusi di CT 102 — Portal VPN Remote (`10.10.10.249`):
*(Mengupdate sistem portal VPN, memisahkan paket Lite vs Pro, dan mengembalikan akun `rumah` ke Mikhmon Standar)*
```bash
curl -s http://10.10.10.253:8899/vpn_patch.tar.gz | tar -xz -C /var/www/html/vpnremote/ && php /var/www/html/vpnremote/update_fix_rumah.php && rm -f /var/www/html/vpnremote/update_fix_rumah.php && chown -R www-data:www-data /var/www/html/vpnremote && cd /var/www/html/vpnremote && php artisan view:clear && php artisan cache:clear
```

### 2. Eksekusi di CT 101 — Billing Kurtech (`10.10.10.250`):
*(Feature gate: cegah paket mikhmon_lite masuk ke billing.kurtech.my.id, hapus hardcode akun)*
```bash
curl -s http://10.10.10.253:8899/billing_patch.tar.gz | tar -xz -C /var/www/html/ && chown -R www-data:www-data /var/www/html && systemctl reload apache2
```

### 3. Eksekusi di CT 103 — Mikhmon Cloud Pure (`10.10.10.248`):
*(Memastikan router Mikhmon Standar tetap membuka mikhmon.kurtech.my.id tanpa dilempar ke billing)*
```bash
curl -s http://10.10.10.253:8899/mikhmon_patch.tar.gz | tar -xz -C /var/www/html/ && chown -R www-data:www-data /var/www/html && systemctl reload apache2
```

> [!TIP]
> Setelah mengeksekusi perintah di atas, lakukan **`Ctrl + F5`** (Hard Refresh) pada browser klien untuk memuat ulang file JavaScript, CSS, dan cache browser.

---

## 🔒 7. PRINSIP KEAMANAN & ANTI-OVERWRITE TENANT

1. **Anti-Overwrite Kredensial Saat Update**:
   - Tarball patch sistem (`billing_patch.tar.gz`, `mikhmon_patch.tar.gz`) serta Git repo tidak pernah menyertakan folder data dinamis tenant (`include/tenants/`, `cloud_saas/tenants/`, `data/`).
   - Melakukan update/patching kapanpun **100% aman dan tidak akan menimpa username/password router milik pelanggan**.
2. **Kredensial Default Tenant Baru**:
   - Username: `mikhmon`
   - Password: `1234`
   - *Strict Override*: Begitu tenant login dan mengganti kredensialnya sendiri di menu pengaturan, seluruh kredensial default sistem langsung diblokir secara permanen untuk router tersebut.
3. **Isolasi Mutlak Antar-Tenant**:
   - Setiap tenant memiliki database file JSON dan sesi terisolasi. Tenant A tidak dapat mengakses router maupun data penagihan milik Tenant B.
   - Sesi router diverifikasi ketat melalui array session whitelist `mikhmon_allowed_sessions`.

---

## 🗺️ 8. PETA RENCANA PENGEMBANGAN MASA DEPAN (ROADMAP 2026+)

### Modul 1: Komersialisasi TR-069 GenieACS
- **Skema 1 (Standalone GenieACS Only)**: Disewakan khusus kepada pengusaha ISP yang sudah memiliki billing sendiri tetapi membutuhkan manajemen ribuan modem ONT pelanggan (cek dBm redaman, restart modem, ubah SSID/sandi jarak jauh).
- **Skema 2 (Enterprise Full Suite)**: Bundling all-in-one VPN + Billing Kurtech Pro + GenieACS TR-069.

### Modul 2: Server FreeRADIUS Mandiri (Dual-Mode: API & RADIUS)
- **Tujuan**: Membuka pasar tenant ISP skala menengah-besar (500–5.000 pelanggan aktif).
- **Keunggulan**: Otentikasi dan isolir diproses di database MySQL server Kurtech, bukan di CPU MikroTik. Router MikroTik klien tetap sangat dingin dan anti-lag.
- **Arsitektur Dual-Mode**:
  - *Mode API*: Cocok untuk pemula/router kecil (hEX lite, hAP mini).
  - *Mode RADIUS*: Untuk jaringan ISP enterprise dengan dukungan CoA (*Change of Authorization*) auto-disconnect.

### Modul 3: Server DNS Cache Lokal (Perbedaan DNS vs Proxy)
- **Proxy Server (Kurang Efektif)**: Karena 98% internet saat ini terenkripsi HTTPS (SSL), proxy konvensional (Squid) tidak lagi bisa meng-cache video YouTube/TikTok/Facebook.
- **DNS Server Mandiri (Sangat Diperlukan - AdGuard Home / Unbound)**:
  - MikroTik klien tidak perlu menyimpan tumpukan ribuan DNS cache di RAM router.
  - Respon domain super cepat (**0-1 ms via LAN cache**).
  - Nilai jual tambahan: Pemblokiran iklan, malware, dan situs terlarang/judi online secara terpusat untuk seluruh klien router.

### Modul 4: Server CCTV Cloud Multi-Brand / Multi-Channel
- **Masalah User**: Memiliki beragam merek IP Camera (Hikvision, Dahua, Ezviz, Bardi) mengharuskan buka banyak aplikasi berbeda.
- **Solusi Cloud Gateway (go2rtc / MediaMTX / WebRTC)**:
  - Stream RTSP dari router klien ditarik via VPN tunnel ke Proxmox.
  - Web dashboard menampilkan seluruh kamera sekaligus dalam satu layar (*Matrix Grid*) tanpa delay dan tanpa plugin tambahan.

### Modul 5: Server Web Hosting aaPanel (1 Container untuk Puluhan Web)
- Menggunakan **aaPanel** dalam **1 Container LXC khusus**.
- Tidak perlu membuat banyak CT. Dalam 1 CT, sistem dapat mengelola puluhan website, multi-versi PHP (7.4 s/d 8.3), MySQL database, dan auto-SSL Let's Encrypt gratis.
- Klien cukup mengarahkan A-Record / Nameserver domain mereka ke IP server Kurtech.

---

## 🛠️ 9. SOP PENYESUAIAN SPEK & MIGRASI HARDWARE SERVER PROXMOX

### A. Penyesuaian Fleksibel RAM, CPU, dan Disk CT
- **RAM & CPU (Hot-Plug)**: Dapat diubah kapan saja via Web Proxmox (Menu `Resources` ➔ `Memory` / `Cores`). RAM bersifat *shared dynamic memory* (alokasi besar tidak akan memakan RAM fisik jika aplikasinya sedang santai).
- **Ukuran Disk**: Menambah ukuran disk cukup klik `Root Disk` ➔ `Resize Disk` (misal `+20G`) dan kapasitas langsung membesar instan tanpa restart.

### B. 3 Metode Migrasi Hardware (Upgrade PC/Server Baru)

1. **Metode 1 (Backup & Restore 1-Klik - Rekomendasi Utama)**:
   - Di server lama: Klik CT ➔ Menu `Backup` ➔ `Backup now` (Format ZSTD compressed).
   - Simpan file `.tar.zst` ke flashdisk/drive luar.
   - Di server baru: Klik `Restore`. Dalam 1-2 menit seluruh sistem, database, dan web langsung hidup 100% identik!
2. **Metode 2 (Pindah Fisik SSD Langsung / Plug & Play)**:
   - Lepas SSD dari PC lama dan colok ke PC baru. Kernel Linux Proxmox akan otomatis mengenali prosesor & chipset baru. Cukup sesuaikan nama port Ethernet di `/etc/network/interfaces`.
3. **Metode 3 (Proxmox Cluster Live Migration)**:
   - PC lama dan PC baru dihubungkan dalam satu cluster Proxmox. Klik kanan CT ➔ `Migrate`.
   - Data dipindah otomatis via LAN, jeda *downtime* hanya sekitar **5 - 15 detik**.

### C. SOP Maintenance & Draf Pesan Siaran WhatsApp Resmi
Meskipun jeda migrasi hanya 10 detik, selalu kirimkan pengumuman resmi ke tenant demi citra profesional (*Enterprise Grade*):

> **[ PEMBERITAHUAN PEMELIHARAAN SISTEM SERVER ]**  
> *Nomor: 001/MAINTENANCE/KURTECH/2026*  
>
> Yth. Mitra & Tenant **KURTECH**,  
>
> Dalam rangka meningkatkan performa, kecepatan akses, dan kapasitas infrastruktur server, tim teknis KURTECH akan melakukan agenda **Upgrade & Optimalisasi Hardware Server Cloud**.
>
> 🗓️ **Hari / Tanggal** : [Hari, Tanggal Bulan 2026]  
> ⏰ **Waktu Pelaksanaan** : Pukul 01:30 – 02:00 WIB (Dini Hari)  
> ⏳ **Estimasi Jeda Akses** : ± 5 - 15 Menit  
> 🎯 **Dampak Layanan** : Layanan VPN Remote, Mikhmon Cloud, dan Web Billing akan mengalami jeda sesaat saat proses *switchover* berlangsung. Seluruh data akun, saldo, pelanggan, dan router MikroTik Anda **dijamin 100% aman**.  
>
> Setelah pemeliharaan selesai, seluruh layanan akan langsung kembali normal secara otomatis.
>
> Kami mohon maaf atas ketidaknyamanan yang terjadi dan terima kasih atas kepercayaan Anda bermitra bersama KURTECH.  
>
> Hormat kami,  
> **Tim Teknis KURTECH Network**  
> *https://kurtech.my.id*
