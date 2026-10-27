# Instruksi untuk Web Super Admin dan Tenant

## 🎯 Tujuan
Dokumen ini menjelaskan peran **Super Admin** dan **Tenant** pada aplikasi VPN Remote, serta contoh akun yang dapat digunakan untuk pengujian dan pengembangan.

---

## 1️⃣ Super Admin

- **Peran**: Memiliki kontrol penuh atas seluruh sistem, termasuk manajemen tenant, konfigurasi VPN, dan pengaturan keamanan.
- **Fitur Utama**:
  - Buat, edit, hapus tenant.
  - Lihat semua log aktivitas.
  - Kelola kunci enkripsi dan sertifikat.
  - Atur kebijakan akses jaringan.

### 📋 Contoh Akun Super Admin
| Username | Password | Email | Hak Akses |
|----------|----------|-------|-----------|
| admin@kurtech | 999Bekasi@ | admin@kurtech | Semua (Full Access) |

> **Catatan**: Akun ini hanya untuk lingkungan **development**. Jangan gunakan di produksi.

---

## 2️⃣ Tenant (Penyewa)

- **Peran**: Mengelola VPN untuk organisasi mereka sendiri. Tidak dapat mengakses atau memodifikasi tenant lain.
- **Fitur Utama**:
  - Buat dan kelola koneksi VPN untuk pengguna internal.
  - Mengatur policy jaringan khusus tenant.
  - Mengakses dashboard statistik penggunaan VPN.

### 📋 Contoh Akun Tenant
| Tenant ID | Username | Password | Email | Hak Akses |
|-----------|----------|----------|-------|-----------|
| tenant-01 | tenantadmin | TenantPass01! | admin@tenant01.com | Manajemen VPN Tenant |
| tenant-02 | tenantadmin2 | TenantPass02! | admin@tenant02.com | Manajemen VPN Tenant |

> **Tip**: Setiap tenant memiliki ruang nama (namespace) terisolasi, sehingga kredensial satu tenant tidak dapat dipakai untuk mengakses tenant lain.

---

## 3️⃣ Cara Mengakses Dashboard
1. Buka browser dan navigasikan ke https://vpn-remote.local.
2. Pilih **Login** di pojok kanan atas.
3. Masukkan **Username** dan **Password** sesuai tabel di atas.
4. Setelah login, Super Admin akan melihat menu **Tenant Management** di sidebar, sementara Tenant hanya melihat **VPN Dashboard** dan **Statistics**.

---

## 4️⃣ Keamanan & Praktik Terbaik
- Ganti password default pada **first login**.
- Aktifkan **Two-Factor Authentication (2FA)** untuk akun Super Admin.
- Batasi akses IP untuk endpoint admin menggunakan firewall.
- Simpan **API keys** dan **certificate** di tempat yang aman (mis. Azure Key Vault, AWS Secrets Manager).

---

## 📚 Referensi Tambahan
- [Dokumentasi API VPN Remote](/api-docs)
- [Panduan Instalasi](/setup-guide)
- [Best Practices Keamanan VPN](/security-best-practices)

---

*Dokumen ini dibuat otomatis oleh Antigravity AI pada *2026-08-16*.*
