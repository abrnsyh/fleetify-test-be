# Attendance Management API

API untuk manajemen absensi, karyawan, dan departemen.

## Setup

1. Clone repo ini.
2. Jalankan `composer install`
3. Copy `.env.example` ke `.env` dan sesuaikan konfigurasi database.
4. Jangan lupa rubah SESSION_DOMAIN di .env sesuai domain lokal Anda, misal: `SESSION_DOMAIN=localhost`
5. Jalankan migrasi:  
   ```
   php artisan migrate --seed
   ```
6. Jalankan server:  
   ```
   php artisan serve
   ```

## User Default
- Email: admin@mail.com
- Password: password

### Ringkasan Endpoint

#### Auth
- `POST /api/login` — Login user
- `POST /api/logout` — Logout user (butuh autentikasi)
- `GET /api/user` — Data user yang sedang login

#### Attendance
- `POST /api/clock-in` — Clock in karyawan
- `PUT /api/clock-out` — Clock out karyawan
- `GET /api/attendances` — List absensi (filter: date, department_id, per_page)

#### Department
- `GET /api/departments` — List department (filter: search, page, limit)
- `GET /api/departments/options` — List department (id & nama, untuk dropdown)
- `POST /api/departments` — Tambah department
- `PUT /api/departments/{id}` — Update department
- `DELETE /api/departments/{id}` — Hapus department

#### Employee
- `GET /api/employees` — List employee (filter: search, page, limit)
- `POST /api/employees` — Tambah employee
- `PUT /api/employees/{id}` — Update employee
- `DELETE /api/employees/{id}` — Hapus employee

### Format Response

- **Sukses:**
  ```json
  {
    "success": true,
    "data": ...,
    "message": "..."
  }
  ```
- **Error:**
  ```json
  {
    "success": false,
    "message": "...",
    "data": ...
  }
  ```

### Autentikasi

- Endpoint selain `/login`, `/clock-in`, dan `/clock-out` membutuhkan autentikasi Sanctum.
- Auth menggunakan sanctum jadi dari frontend tidak butuh mengirimkan auth token di header.
