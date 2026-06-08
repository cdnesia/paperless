# E-OFFICE

Sistem manajemen surat-menyurat digital berbasis web untuk **Universitas Muhammadiyah Jambi**. Mendukung pembuatan, pengiriman, disposisi, dan verifikasi surat keluar — dengan tanda tangan digital dan notifikasi Telegram.

---

## 🛠️ Teknologi

| Layer | Teknologi |
|---|---|
| Framework | Laravel 13 |
| PHP | ^8.3 |
| Database | MariaDB / MySQL |
| Auth | Session-based + Spatie Permission (RBAC) |
| Frontend | Blade + Bootstrap 5 + jQuery + Select2 + Tagify |
| QR Code | simplesoftwareio/simple-qrcode |
| PDF | barryvdh/laravel-dompdf + setasign/fpdi |
| Google Docs | google/apiclient |
| Telegram | Custom notification service |

---

## 📦 Instalasi

```bash
git clone <repo-url>
cd E-OFFICE
composer install
cp .env.example .env
# sesuaikan DB_USERNAME, DB_PASSWORD, dll di .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Login default: `superadmin@eoffice.test` / `password`

---

## 🔐 Role & Permission (RBAC)

Sistem menggunakan **Spatie Permission** dengan middleware `route.permission` yang otomatis mengecek permission berdasarkan nama route.

| Role | Akses |
|---|---|
| **Super Admin** | Full access — semua menu termasuk Pengaturan, Role, Permission, dan Migrasi |
| **Rektor / Wakil Rektor** | Dashboard, Surat Keluar, Surat Masuk, Disposisi, Tanda Tangan Digital |
| **Dekan** | Dashboard, Surat Keluar (baca & disposisi), Surat Masuk, Disposisi, TTD |
| **Kaprodi / Ketua / Kepala** | Dashboard, Surat Keluar (baca & disposisi), Surat Masuk, Disposisi |
| **Sekretaris** | Dashboard, Surat Keluar (CRUD + kirim), Surat Masuk, Disposisi, TTD |
| **Staff** | Dashboard, Surat Masuk, Disposisi Masuk |
| **User Biasa** | Dashboard, Surat Masuk, Disposisi Masuk (minimal) |

---

## 📊 Alur Kerja

### 1. Surat Keluar
```
Buat Surat → Draft → [Edit/Finalisasi] → Kirim → Penerima dapat notifikasi
                                                        ↓
                                               Tanda Tangan Digital
                                                        ↓
                                               Verifikasi via QR Code
```

### 2. Alur Disposisi
```
Surat Masuk (penerima)
    ↓
Klik "Aksi" → Disposisikan / Teruskan → pilih penerima lanjutan
    ↓
Penerima lanjutan dapat di Disposisi Masuk
    ↓
Bisa lanjut Terima / Tolak / Teruskan lagi
```

### 3. Tanda Tangan Digital
```
PDF Surat → Hash SHA256 → Generate QR Code → PDF final (stamped)
    ↓
QR Code bisa dipindai → halaman verifikasi publik
    ↓
Cek hash original vs sekarang → valid/tidak
```

---

## 📁 Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── DisposisiController.php
│   │   ├── FileController.php
│   │   ├── MigrationController.php
│   │   ├── PengaturanController.php
│   │   ├── PermissionController.php
│   │   ├── ProfileController.php
│   │   ├── RoleController.php
│   │   ├── SuratKeluarController.php
│   │   ├── SuratMasukController.php
│   │   ├── TandaTanganDigitalController.php
│   │   ├── UnitKerjaController.php
│   │   └── UserController.php
│   └── Middleware/
│       └── CheckRoutePermission.php   # Otomatis cek permission via route name
├── Models/
│   ├── Disposisi.php
│   ├── LoginHistory.php
│   ├── PenerimaSurat.php
│   ├── Pengaturan.php
│   ├── SuratKeluar.php
│   ├── SuratKeluarHistory.php
│   ├── TandaTanganDigital.php
│   ├── UnitKerja.php
│   └── User.php
├── Services/
│   ├── ApiService.php
│   ├── GoogleDocsService.php
│   └── TelegramNotificationService.php
└── Traits/
    └── ApiResponse.php

database/
├── migrations/     # Semua migrasi
└── seeders/
    ├── DatabaseSeeder.php
    ├── RoleSeeder.php      # Semua role & permission
    └── UserSeeder.php      # Super Admin default
```

---

## 🔗 Routes Utama

| Prefix | Nama Route | Keterangan |
|---|---|---|
| `/surat-keluar` | `surat-keluar.*` | CRUD surat keluar + kirim + history + disposisi |
| `/surat-masuk` | `surat-masuk.*` | Inbox surat dari penerima + aksi Terima/Tolak/Teruskan |
| `/disposisi` | `disposisi.*` | Disposisi keluar (yang dikirim user) |
| `/disposisi-masuk` | `disposisi-masuk.*` | Disposisi masuk (yang diterima user) + teruskan |
| `/tanda-tangan-digital` | `tanda-tangan-digital.*` | Tanda tangan + verifikasi publik |
| `/roles` | `roles.*` | Manajemen role (super-admin only) |
| `/permissions` | `permissions.*` | Manajemen permission + sync otomatis |
| `/users` | `users.*` | Manajemen user |
| `/unit-kerja` | `unit-kerja.*` | Unit kerja |
| `/pengaturan` | `pengaturan.*` | Pengaturan sistem + Telegram |
| `/migration` | `migration.*` | Migrasi data dari DB lama (super-admin only) |
| `/files/{path}` | `files.serve` | Serve file dari storage (publik) |

---

## 🪝 Middleware Permission

Route-level permission check otomatis oleh `CheckRoutePermission`:

- Setiap nama route dicek sebagai permission di Spatie
- **Super Admin** selalu bypass
- Route yang dikecualikan: `login`, `logout`, `dashboard`, `profile.*`, `tanda-tangan-digital.verify`
- Jika user tidak punya permission → **403 Forbidden**

---

## 📱 Notifikasi Telegram

Sistem mengirim notifikasi otomatis ke Telegram saat:
- Surat dikirim ke penerima
- Disposisi masuk diteruskan

Konfigurasi di halaman **Pengaturan → Sistem**:
- `telegram_bot_token` — Bot token dari @BotFather
- `telegram_notif_surat_masuk` — Aktifkan notifikasi surat masuk
- `telegram_tpl_surat_masuk` — Template pesan
- `telegram_notif_disposisi_masuk` — Aktifkan notifikasi disposisi masuk
- `telegram_tpl_disposisi_masuk` — Template pesan

User harus punya `telegram_chat_id` di profil.

---

## 📄 Google Docs Integration

Saat membuat surat keluar dengan metode **Google Docs**:
1. Sistem copy template dari Google Drive
2. Replace placeholder `{nomor_surat}`, `{perihal}`, `{tanggal_surat}`
3. User bisa edit di Google Docs
4. Saat final → export ke PDF, simpan di storage, hapus dari Drive

---

## ✅ Fitur

- [x] Surat keluar (upload PDF / Google Docs)
- [x] Multi-penerima surat keluar
- [x] Disposisi berantai (teruskan → teruskan → terima/tolak)
- [x] Tanda tangan digital + QR code
- [x] Verifikasi keaslian via QR scan (publik)
- [x] Role-based access control (6 role)
- [x] Permission otomatis dari route name
- [x] Notifikasi Telegram
- [x] Audit trail surat keluar
- [x] File serve dengan fallback "File Tidak Tersedia"
- [x] Migrasi data dari sistem lama
- [x] Tagify dropdown untuk pilih penerima disposisi

---

## 🔧 Pengembangan

```bash
# Format code
./vendor/bin/pint

# Run test
php artisan test

# Sync permission dari routes
php artisan permissions:sync   # atau via halaman /permissions
```

---

<div align="center">
  <sub>© 2026 ICT CENTER — Universitas Muhammadiyah Jambi</sub>
</div>

