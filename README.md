# PMB (Penerimaan Mahasiswa Baru) - {{ config('app.name') }}

Sistem Informasi Penerimaan Mahasiswa Baru berbasis web dengan Laravel 11.

## Fitur

### Frontend
- ✅ Halaman Beranda dengan hero section, statistik, program studi, dan berita terbaru
- ✅ Halaman Profil Kampus (Visi & Misi)
- ✅ Halaman Program Studi dengan detail
- ✅ Halaman Berita dengan kategori dan pencarian
- ✅ Halaman Jadwal PMB
- ✅ Halaman Biaya Kuliah
- ✅ Halaman FAQ
- ✅ Halaman Kontak
- ✅ Halaman Statis (dinamis)
- ✅ Desain modern dengan Bootstrap 5, AOS animations
- ✅ Responsive mobile-friendly

### Admin Panel
- ✅ Dashboard dengan statistik
- ✅ Manajemen Menu (multi-level)
- ✅ Manajemen Kategori Berita
- ✅ Manajemen Berita (dengan upload thumbnail)
- ✅ Manajemen Program Studi (dengan upload icon & thumbnail)
- ✅ Manajemen Jadwal PMB
- ✅ Manajemen Halaman Statis
- ✅ Manajemen Admin
- ✅ Dark theme dengan Bootstrap 5
- ✅ DataTables & Select2 integration

## Persyaratan

- PHP 8.1+
- Composer
- MySQL/MariaDB
- Node.js & NPM (untuk asset compilation)

## Instalasi

1. Clone repository:
```bash
git clone https://github.com/yourusername/penmaru.git
cd penmaru
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Copy environment:
```bash
cp .env.example .env
```

4. Generate key:
```bash
php artisan key:generate
```

5. Konfigurasi database di `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=penmaru
DB_USERNAME=root
DB_PASSWORD=
```

6. Jalankan migrasi dan seeder:
```bash
php artisan migrate --seed
```

7. Buat storage link:
```bash
php artisan storage:link
```

8. Jalankan development server:
```bash
php artisan serve
```

## Login Default

### Admin Panel
- URL: `http://localhost:8000/admin/login`
- Email: `admin@example.com`
- Password: `password`

### Frontend User
- URL: `http://localhost:8000/login`
- Email: `admin@penmaru.com`
- Password: `password`

## Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin controllers
│   │   ├── AuthController.php
│   │   ├── BeritaController.php
│   │   ├── FrontendController.php
│   │   └── ...
│   └── Middleware/
│       └── AdminAuth.php
├── Models/
│   ├── Admin.php
│   ├── Berita.php
│   ├── HalamanStatis.php
│   ├── JadwalPMB.php
│   ├── KategoriBerita.php
│   ├── Menu.php
│   └── ProgramStudi.php
resources/views/
├── admin/                   # Admin panel views
│   ├── layouts/
│   ├── auth/
│   ├── dashboard.blade.php
│   ├── menu/
│   ├── kategori-berita/
│   ├── berita/
│   ├── program-studi/
│   ├── jadwal-pmb/
│   ├── halaman-statis/
│   └── admin/
├── frontend/                # Frontend views
│   ├── layouts/
│   ├── home.blade.php
│   ├── profil.blade.php
│   ├── program-studi.blade.php
│   ├── program-studi-detail.blade.php
│   ├── jadwal.blade.php
│   ├── biaya.blade.php
│   ├── faq.blade.php
│   ├── kontak.blade.php
│   ├── halaman.blade.php
│   └── berita/
└── auth/
    └── login.blade.php
database/
├── migrations/              # 7 migration files
└── seeders/
    └── DatabaseSeeder.php
routes/
└── web.php
```
# paperless
