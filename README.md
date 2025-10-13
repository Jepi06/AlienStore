# AlienStore Backend (Laravel)

Ini adalah backend API Laravel untuk aplikasi e-commerce AlienStore.

## Setup Cepat dengan Laragon

1. **Pastikan Laragon sudah berjalan:**
   - Start Apache dan MySQL dari panel Laragon
   - Buat database `alienstore` melalui phpMyAdmin

2. **Install dependensi:**
   ```bash
   composer install
   ```

3. **Setup environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database di `.env` (untuk Laragon):**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=alienstore
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan migrasi dan seeder:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Jalankan server:**
   ```bash
   php artisan serve
   ```
   Atau gunakan virtual host Laragon

## User Admin Default

Setelah menjalankan `php artisan db:seed`, user admin akan dibuat:

- **Email:** `admin@alienstore.com`
- **Password:** `admin123`
- **Role:** Admin

⚠️ **Ganti password setelah login pertama!**

## Membuat Admin Manual

Anda juga bisa membuat user admin secara manual:

```bash
# Gunakan kredensial default
php artisan admin:create

# Atau dengan kredensial custom
php artisan admin:create --email=admin@example.com --password=mypassword --name="Admin User"
```

## API Endpoints

API tersedia di `http://localhost:8000/api/` atau `http://alienstore-backend.test/api/`

Endpoint utama:
- `POST /api/login` - Autentikasi user
- `POST /api/register` - Registrasi user
- `GET /api/products` - Ambil data produk
- `GET /api/categories` - Ambil data kategori
- `POST /api/carts` - Kelola keranjang belanja
- `POST /api/transaksi` - Buat transaksi

## Troubleshooting

**Pembuatan admin gagal?**
1. Pastikan role sudah ada: `php artisan db:seed --class=SecRoleSeeder`
2. Lalu buat admin: `php artisan admin:create`

**Masalah koneksi database?**
- Periksa pengaturan `.env`
- Pastikan MySQL di Laragon sudah jalan
- Pastikan database `alienstore` sudah dibuat

**Error permission (Windows)?**
- Jalankan Command Prompt sebagai Administrator
- Atau gunakan Laragon Terminal

**Virtual Host Laragon:**
- Pindah project ke `C:\laragon\www\alienstore-backend`
- Restart Laragon
- Akses via `http://alienstore-backend.test`
