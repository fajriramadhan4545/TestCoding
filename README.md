# Ship Maintenance API

API berbasis Laravel 11 untuk mendata kapal dan pencatatan log perawatan (maintenance) kapal. Project ini dilengkapi dengan queue processing, rate limiting, dan validasi input.

---

## Daftar Isi

- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi & Setup](#instalasi--setup)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Queue Worker](#queue-worker)
- [Daftar Endpoint API](#daftar-endpoint-api)
- [Contoh Request & Response](#contoh-request--response)
- [Fitur Keamanan](#fitur-keamanan)
- [Struktur Proyek](#struktur-proyek)

---

## Persyaratan Sistem

| Komponen | Versi |
|----------|-------|
| PHP | >= 8.2 |
| Laravel | 11.x |
| MySQL | >= 8.0 |
| Composer | >= 2.x |

---

## Instalasi & Setup

### 1. Clone Repository

```bash
git clone https://github.com/<username>/ship-maintenance-api.git
cd ship-maintenance-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Copy & Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di file `.env`:

```env
APP_NAME="Ship Maintenance API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ship_maintenance
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

MAIL_MAILER=log
OPERATIONS_MANAGER_EMAIL=manager@shipmaintenance.com
```

> **Catatan:** `MAIL_MAILER=log` akan menyimpan email ke file log (`storage/logs/laravel.log`) tanpa mengirim email sungguhan. Sangat cocok untuk pengembangan lokal.

### 4. Buat Database

```bash
mysql -u root -p -e "CREATE DATABASE ship_maintenance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Jalankan Migrasi & Seeder

```bash
# Reset database, jalankan migrasi, dan seed (50 kapal + 500 log servis)
php artisan migrate:fresh --seed
```

---

## Menjalankan Aplikasi

```bash
php artisan serve
```

Aplikasi berjalan di: `http://localhost:8000`

Base URL API: `http://localhost:8000/api`

---

## Queue Worker

Sistem menggunakan **Laravel Queue** dengan driver `database` untuk memproses pengiriman notifikasi email secara asynchronous.

### Menjalankan Worker

```bash
# Jalankan queue worker (foreground)
php artisan queue:work

# Jalankan hanya satu job (untuk testing)
php artisan queue:work --once

# Jalankan dengan timeout dan sleep
php artisan queue:work --timeout=60 --sleep=3 --tries=3
```

### Melihat Status Job

```bash
# Lihat failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

> **Catatan:** Setiap kali endpoint `PATCH /api/maintenance-logs/{id}/complete` dipanggil, sebuah job `SendServiceCompletedNotification` akan ditambahkan ke queue. Worker harus berjalan agar job diproses. Detail email akan tercatat di `storage/logs/laravel.log`.

---

## Daftar Endpoint API

### Rate Limiting
Semua endpoint API dibatasi maksimal **60 requests per menit** per IP address. Jika melebihi batas, server akan mengembalikan response `HTTP 429 Too Many Requests`.

### Ships (Kapal)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/ships` | Daftar kapal + info servis terakhir + total biaya (Rupiah terformat) |
| `GET` | `/api/ships/{id}` | Detail kapal + statistik servis |

#### Filter & Pencarian untuk `GET /api/ships`

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `search` | string | Cari berdasarkan nama atau kode kapal |
| `min_biaya` | number | Filter minimum total biaya servis (angka murni) |
| `max_biaya` | number | Filter maksimum total biaya servis (angka murni) |
| `status` | string | Filter kapal yang memiliki log terbaru dengan status: `planned`, `ongoing`, `completed` |
| `per_page` | integer | Jumlah item per halaman (default: 15, max: 100) |

### Maintenance Logs (Log Servis)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/maintenance-logs` | Daftar log servis |
| `GET` | `/api/maintenance-logs/{id}` | Detail log servis |
| **`PATCH`** | **`/api/maintenance-logs/{id}/complete`** | **Tandai selesai + auto-schedule + kirim notifikasi** |

#### Filter untuk `GET /api/maintenance-logs`

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `ship_id` | string (UUID) | Filter berdasarkan UUID kapal |
| `status` | string | Filter berdasarkan status: `planned`, `ongoing`, `completed` |
| `date_from` | date | Filter dari tanggal (format: YYYY-MM-DD) |
| `date_to` | date | Filter hingga tanggal (format: YYYY-MM-DD) |
| `per_page` | integer | Jumlah item per halaman (default: 15, max: 100) |

---

## Contoh Request & Response

### GET /api/ships

```bash
curl -X GET "http://localhost:8000/api/ships?status=completed&min_biaya=10000000&per_page=5"
```

**Response:**
```json
{
  "success": true,
  "message": "Data kapal berhasil diambil.",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "019ead17-c80b-7217-89a9-5a18d3b3bd8b",
        "nama": "MV Nusantara A123",
        "kode_kapal": "MV-0123-2015-01",
        "tahun_pembuatan": 2015,
        "total_biaya_servis": "Rp 1.250.000.000",
        "servis_terakhir": {
          "id": "019ead17-c82a-7335-8bfb-8eb86f20aa3e",
          "tanggal_servis": "2026-11-15",
          "jenis_servis": "Perawatan Mesin Utama",
          "biaya": "Rp 85.000.000",
          "status": "completed"
        }
      }
    ],
    "total": 12,
    "per_page": 5,
    "last_page": 3
  }
}
```

### PATCH /api/maintenance-logs/{uuid}/complete

Menandai log servis sebagai **completed**, otomatis menjadwalkan servis rutin berikutnya **6 bulan ke depan**, dan menambahkan notifikasi email ke antrian.

```bash
curl -X PATCH "http://localhost:8000/api/maintenance-logs/019ead17-c82a-7335-8bfb-8eb86f20aa3e/complete"
```

**Response:**
```json
{
  "success": true,
  "message": "Servis berhasil ditandai sebagai selesai. Servis rutin berikutnya telah dijadwalkan.",
  "data": {
    "completed_log": {
      "id": "019ead17-c82a-7335-8bfb-8eb86f20aa3e",
      "ship_id": "019ead17-c80b-7217-89a9-5a18d3b3bd8b",
      "tanggal_servis": "2026-06-01T00:00:00.000000Z",
      "jenis_servis": "Perawatan Mesin Utama",
      "biaya": "Rp 85.000.000",
      "status": "completed"
    },
    "scheduled_log": {
      "id": "019ead17-cfc8-70ad-ab01-2a6d8122d2cc",
      "ship_id": "019ead17-c80b-7217-89a9-5a18d3b3bd8b",
      "tanggal_servis": "2026-12-01T00:00:00.000000Z",
      "jenis_servis": "Perawatan Mesin Utama (Rutin)",
      "biaya": "Rp 85.000.000",
      "status": "planned"
    },
    "next_service_at": "2026-12-01T00:00:00.000000Z"
  }
}
```

### Contoh Error Bisnis Logika (422)

Response jika memanggil endpoint `complete` untuk servis yang sudah berstatus `completed`:

```json
{
  "success": false,
  "message": "Servis ini sudah berstatus 'completed'."
}
```

### Rate Limit Exceeded (429)

```json
{
  "success": false,
  "message": "Terlalu banyak permintaan. Batas: 60 permintaan per menit. Coba lagi nanti.",
  "retry_after": "60"
}
```

---

## Fitur Keamanan

### Rate Limiting (60 RPM)
- Semua endpoint `/api/*` dibatasi **60 requests per menit** per IP address.
- Dikonfigurasi di `app/Providers/AppServiceProvider.php`.
- Response `HTTP 429` saat limit terlampaui.

### Input & Query Parameter Validation
Semua filter input divalidasi dengan aman menggunakan **Form Request Classes**:
- `FilterShipRequest` — validasi parameter pencarian dan penyaringan kapal.
- `FilterMaintenanceLogRequest` — validasi parameter pencarian dan penyaringan log servis.



## Teknologi yang Digunakan

- **Laravel 11** — Framework PHP
- **MySQL / SQLite** — Database
- **Laravel Queue** (driver: database) — Background job processing
- **Laravel Mail** (driver: log) — Email notification simulation
- **Laravel Rate Limiter** — API security (60 RPM)
- **Laravel Form Requests** — Filter parameter validation
