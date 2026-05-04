# KPM-PPL Manager

Aplikasi web Laravel 12 untuk mengelola pendaftaran dan penempatan mahasiswa pada program **KPM** (Kuliah Pengabdian Masyarakat) dan **PPL** (Praktik Pengalaman Lapangan), dengan rekomendasi sekolah berdasarkan jarak dari tempat tinggal mahasiswa.

## Stack
- PHP 8.4 + Laravel 12
- Database: SQLite (`database/database.sqlite`)
- View: Blade + Tailwind CSS via CDN
- Auth: **JWT custom** (firebase/php-jwt) — token disimpan di HTTP-only cookie `kpm_token`
- Geocoding: **OpenStreetMap Nominatim API** (gratis, tanpa API key)

## Akun Seeder Default
| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@kampus.ac.id` | `admin123` |
| Mahasiswa (KPM) | `andi.kosong@kampus.ac.id` | `password123` |
| Mahasiswa (KPM ditempatkan) | `budi.kpm@kampus.ac.id` | `password123` |
| Mahasiswa (PPL ditempatkan) | `citra.ppl@kampus.ac.id` | `password123` |
| Mahasiswa (PKPPM lengkap) | `dewi.keduanya@kampus.ac.id` | `password123` |

Semua mahasiswa sudah `approved`. Mahasiswa baru juga bisa daftar mandiri lewat halaman *Daftar*.

## Alur Mahasiswa
1. Daftar akun (nama, email kampus, password)
2. Lengkapi profil:
   - NIM, no. HP (opsional)
   - **Alamat** + koordinat lat/lng — tersedia tombol:
     - "Cari Koordinat dari Alamat" → memanggil **Nominatim OpenStreetMap**, hasil pencarian dapat diklik untuk auto-fill koordinat
     - "Gunakan lokasi saya saat ini" → pakai geolokasi browser
   - Nilai microteaching: **A, B, C, D, E**
   - Upload **4 berkas wajib**: Transkrip, KTM, Surat Pengantar, Pas Foto
3. Status `pending` → menunggu review admin
4. Setelah `approved`, mahasiswa bisa pilih program **KPM** dan/atau **PPL**
5. Sistem menampilkan rekomendasi sekolah dalam radius (admin yang menentukan) dari tempat tinggal mahasiswa, terurut jarak terdekat — pakai **rumus Haversine**
6. Mahasiswa pilih sekolah → status pendaftaran `pending` → menunggu konfirmasi admin

## Alur Admin
- **Dashboard**: ringkasan jumlah mahasiswa, sekolah, dan penempatan
- **Mahasiswa**: lihat daftar, detail (lengkap dengan link 4 berkas + tautan peta), setujui/tolak/hapus
- **Sekolah**: CRUD (nama, jenjang SD/SMP/SMA/SMK/MI/MTs/MA, koordinat lat/lng, program KPM/PPL/keduanya, kuota KPM, kuota PPL, kontak, status aktif)
- **Penempatan**: lihat semua pendaftaran KPM/PPL, filter program/status, setujui/tolak/hapus
- **Pengaturan**: nama institusi, **radius maksimum (km)**, batas waktu KPM, batas waktu PPL

## Arsitektur Auth (JWT Custom)
- `App\Services\JwtService` — encode/decode JWT pakai `firebase/php-jwt` (HS256, kunci diturunkan dari `APP_KEY`)
- `App\Auth\JwtGuard` — custom Guard yang membaca JWT dari cookie `kpm_token` atau header `Authorization: Bearer ...`
- Diregistrasi di `App\Providers\AppServiceProvider::boot()` via `Auth::extend('jwt', ...)`
- Cookie `kpm_token` dikecualikan dari enkripsi cookie default Laravel (`bootstrap/app.php` → `encryptCookies(except: [...])`)
- TTL token: **7 hari**

## Geocoding (Nominatim)
- Endpoint: `GET /api/geocode?q=...` (auth required)
- File: `app/Http/Controllers/GeocodeController.php`
- Memanggil `https://nominatim.openstreetmap.org/search` dengan `User-Agent` & `countrycodes=id`
- Tidak butuh API key, tetapi tunduk pada [Usage Policy Nominatim](https://operations.osmfoundation.org/policies/nominatim/) — max 1 req/s

## Struktur Database
- `users` — id, name, email, password, **role** (`admin`/`mahasiswa`)
- `mahasiswa_profiles` — user_id, nim, phone, address, lat/lng, microteaching_grade (A-E), `transkrip_path`, `ktm_path`, `surat_pengantar_path`, `pas_foto_path`, status (pending/approved/rejected), admin_note
- `schools` — name, jenjang, address, lat/lng, program (KPM/PPL/BOTH), kuota_kpm, kuota_ppl, kontak, is_active
- `registrations` — mahasiswa_profile_id, school_id, program (KPM/PPL), distance_km, status (pending/approved/rejected/cancelled). Unique pada `(mahasiswa_profile_id, program)`
- `settings` — key/value (radius, deadline, dll.)

## Perintah Penting
```bash
php artisan migrate:fresh --seed   # reset DB + seed admin + mahasiswa + sekolah contoh
php artisan storage:link           # symlink public/storage (sudah dijalankan)
php artisan serve --host=0.0.0.0 --port=5000   # server (workflow)
```

## Workflow
- **Start application**: `php artisan serve --host=0.0.0.0 --port=5000` (port 5000, webview)

## Catatan
- Trusted proxies di-set ke `*` (`bootstrap/app.php`) agar URL Laravel mengenali HTTPS dari proxy Replit
- Upload file disimpan di `storage/app/public/mahasiswa/{user_id}/...` dan diakses via `/storage/...`
- Untuk produksi, ganti Tailwind CDN dengan build Vite (`npm install && npm run build`)
