# ScentVault Backend API

Backend API untuk aplikasi manajemen koleksi parfum, scent log, dan rekomendasi parfum berbasis kondisi cuaca serta lokasi user.

## Fitur Utama

- Authentication API dengan Laravel Sanctum (`register`, `login`, `logout`)
- Manajemen profil user dan update region user
- Master data parfum: brand, category, notes, occasion
- Manajemen koleksi parfum milik user
- Upload gambar parfum ke storage publik
- Pengaturan suitability parfum:
  - ideal_temperature: `dingin|normal|panas`
  - ideal_time: `pagi|siang|malam`
  - ideal_environment: `indoor|outdoor|all around`
- Scent log pemakaian parfum
- Rekomendasi parfum berdasarkan:
  - lokasi user
  - prakiraan cuaca BMKG
  - rule config suhu dan waktu
  - rating parfum dari user
- Manajemen user dan rule config untuk admin
- Monitoring status integrasi BMKG dan master region
- Dokumentasi API otomatis dengan Scramble

## Tech Stack

- PHP `^8.3`
- Laravel `^13`
- Laravel Sanctum
- MySQL
- Vite
- Scramble API Docs

## Prasyarat

Pastikan environment lokal sudah memiliki:

- PHP 8.3+
- Composer
- Node.js 18+ dan npm
- MySQL / MariaDB
- Internet aktif untuk:
  - sinkronisasi region dari `wilayah.id`
  - request cuaca ke BMKG

## Konfigurasi Environment

Copy file env:

```bash
cp .env.example .env
```

Atur minimal value berikut di `.env`:

```env
APP_NAME="ScentVault Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scentvault_backend
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

BMKG_BASE_URL=https://api.bmkg.go.id/publik
BMKG_TIMEOUT=10
BMKG_CACHE_MINUTES=10
```

## Setup Lengkap dari Nol

1. Clone repository

```bash
git clone <repository-url>
cd ScentVault-backend
```

2. Install dependency backend

```bash
composer install
```

3. Install dependency frontend/build tools

```bash
npm install
```

4. Copy `.env` lalu sesuaikan konfigurasi database

```bash
cp .env.example .env
```

5. Buat database MySQL

```sql
CREATE DATABASE scentvault_backend;
```

6. Generate application key

```bash
php artisan key:generate
```

7. Jalankan migration dan seeder

```bash
php artisan migrate --seed
```

8. Buat symbolic link storage publik

```bash
php artisan storage:link
```

9. Sinkronkan master region Indonesia

```bash
php artisan app:sync-region
```

10. Jalankan server API

```bash
php artisan serve
```

11. Jika ingin mode development lengkap, jalankan:

```bash
composer dev
```

Perintah `composer dev` akan menjalankan:

- Laravel dev server
- queue listener
- Vite dev server

## Jalur Cepat

Project ini punya script bawaan:

```bash
composer setup
```

Tetapi setelah itu kamu tetap disarankan menjalankan:

```bash
php artisan db:seed
php artisan storage:link
php artisan app:sync-region
```

Karena `composer setup` belum melakukan seed data, storage link, dan sync region.

## Seed Data Default

Seeder bawaan saat ini membuat:

- 1 admin:
  - email: `admin@scentvault.com`
  - password: `password`
- 10 user dummy dari factory
- category default
- occasion default
- rule config default untuk temperature dan time

Catatan: segera ganti password admin setelah setup.

## Dokumentasi API

Setelah server berjalan, buka:

- UI docs: `http://127.0.0.1:8000/docs/api`
- OpenAPI JSON: `http://127.0.0.1:8000/docs/api.json`

## Ringkasan Endpoint

### Public

- `POST /api/register`
- `POST /api/login`
- `GET|POST|PUT|DELETE /api/brands`
- `GET|POST|PUT|DELETE /api/categories`
- `GET|POST|PUT|DELETE /api/notes`
- `GET|POST|PUT|DELETE /api/occasions`
- `GET /api/region/provinces`
- `GET /api/region/regencies?province_code=...`
- `GET /api/region/districts?regency_code=...`
- `GET /api/region/villages?district_code=...`

### Authenticated (`Authorization: Bearer <token>`)

- `GET /api/me`
- `PATCH /api/me/region`
- `POST /api/logout`
- `GET|POST|PUT|DELETE /api/perfumes`
- `GET|PUT /api/perfumes/{id}/suitability`
- `GET|POST|PUT|DELETE /api/scentLog`
- `GET /api/recommendations/current`

- `GET /api/pages/perfume-collection`

#### Query Parameters:
- `category_id: optional, ID kategori yang dipilih`
- `page: optional, nomor halaman pagination`
- `sort: optional, newest|oldest`
- `per_page: optional, jumlah item per halaman`

`Contoh:`
- `/api/pages/perfume-collection`
- `/api/pages/perfume-collection?category_id=2`
- `/api/pages/perfume-collection?page=2`
- `/api/pages/perfume-collection?category_id=2&page=2`


### Admin Only

- `GET|POST|PUT|DELETE /api/users`
- `GET|POST|PUT|DELETE /api/rule-configs`
- `GET /api/integration-status`

## Flow Rekomendasi

Agar endpoint `GET /api/recommendations/current` bekerja dengan benar:

1. User harus login
2. User harus punya `region_code`
3. Data `region` harus sudah di-sync
4. User harus punya minimal 1 perfume
5. Perfume harus punya data suitability
6. Tabel `rule_configs` harus terisi
7. Server harus bisa mengakses API BMKG

## Upload Gambar

Upload gambar parfum disimpan ke disk `public`, jadi endpoint image URL baru akan bekerja dengan benar jika sudah menjalankan:

```bash
php artisan storage:link
```

## Catatan Tambahan

- Default database pada `.env.example` adalah MySQL, bukan SQLite
- Session, cache, dan queue memakai driver database
- Jika BMKG atau `wilayah.id` tidak bisa diakses, fitur rekomendasi dan sync region akan terganggu
