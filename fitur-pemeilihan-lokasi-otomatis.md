# Fitur Pemilihan Lokasi Otomatis — KPM & PPL

> Dokumentasi teknis sistem penetapan lokasi KPM (Desa) dan PPL (Sekolah) secara otomatis berdasarkan domisili mahasiswa.

---

## Daftar Isi

1. [Ringkasan Fitur](#1-ringkasan-fitur)
2. [Arsitektur & Alur Sistem](#2-arsitektur--alur-sistem)
3. [API Pihak Ketiga yang Digunakan](#3-api-pihak-ketiga-yang-digunakan)
4. [Skema Database](#4-skema-database)
5. [Sisi Mahasiswa — Pemilihan Lokasi](#5-sisi-mahasiswa--pemilihan-lokasi)
6. [Sisi Admin — Input Lokasi Sekolah](#6-sisi-admin--input-lokasi-sekolah)
7. [Algoritma Haversine (Perhitungan Jarak)](#7-algoritma-haversine-perhitungan-jarak)
8. [Logika Filter Radius & Rekomendasi](#8-logika-filter-radius--rekomendasi)
9. [Daftar File Penting](#9-daftar-file-penting)
10. [Cara Menerapkan di Project Lain](#10-cara-menerapkan-di-project-lain)

---

## 1. Ringkasan Fitur

Sistem ini menetapkan lokasi KPM (Kuliah Pengabdian Masyarakat) dan PPL (Praktik Pengalaman Lapangan) untuk setiap mahasiswa **secara otomatis** — tanpa mahasiswa memilih sendiri. Penempatan dipicu ketika admin menyetujui profil mahasiswa.

| Program | Lokasi Tujuan | Basis Penetapan |
|---------|--------------|-----------------|
| **KPM** | Desa / Kelurahan | Lokasi terdekat dari domisili mahasiswa dengan kuota tersedia |
| **PPL** | Sekolah (SD–MA/SMK) | Lokasi terdekat dari domisili mahasiswa dengan kuota tersedia |

**Dua kondisi penetapan:**

- **Kondisi 1 — Berbasis Radius:** Sistem mencari desa/sekolah terdekat dari koordinat domisili mahasiswa dalam radius yang dapat dikonfigurasi (default 10 km). Jika ditemukan lokasi dengan kuota tersedia, langsung ditetapkan.
- **Kondisi 2 — Berbasis Kedekatan Antar-Lokasi:** Jika tidak ada lokasi dalam radius, sistem mencari pasangan desa KPM + sekolah PPL yang saling paling berdekatan satu sama lain (meminimalkan jarak tempuh mahasiswa antar dua lokasi penempatan).

---

## 2. Arsitektur & Alur Sistem

```
Mahasiswa mengisi profil
  │
  ▼
Mahasiswa mengisi alamat domisili
  │  ↳ Browser mengirim koordinat (latitude/longitude) via geocoding
  │    atau GPS browser
  ▼
Admin mereview profil
  │
  ▼
Admin klik "Setujui & Tetapkan Penempatan"
  │
  ▼
MahasiswaManagementController@approve()
  │
  ├─► Gelombang::activeFor('KPM') → cek apakah gelombang KPM aktif & terbuka
  ├─► Gelombang::activeFor('PPL') → cek apakah gelombang PPL aktif & terbuka
  │
  ▼
AutoAssignService@assign(MahasiswaProfile $profile)
  │
  ├─ [Kondisi 1] findNearestWithinRadius()
  │     • Ambil semua lokasi aktif dengan program KPM atau PPL
  │     • Hitung jarak ke setiap lokasi dengan formula Haversine
  │     • Filter: jarak ≤ max_radius_km DAN kuota tersedia > 0
  │     • Ambil yang terdekat
  │
  ├─ [Kondisi 2] assignByProximity()
  │     • Jika 1 program belum terpenuhi: cari lokasi terdekat dari
  │       lokasi program lain yang sudah ditetapkan (atau dari domisili)
  │     • Jika 2 program belum terpenuhi: iterasi semua kombinasi
  │       (desa_kpm × sekolah_ppl), pilih pasangan dengan jarak terkecil
  │
  ▼
Registration::create() → simpan ke tabel registrations
  │  • mahasiswa_profile_id
  │  • school_id (desa untuk KPM, sekolah untuk PPL)
  │  • gelombang_id
  │  • program ('KPM' atau 'PPL')
  │  • distance_km (jarak domisili ke lokasi)
  │  • status = 'pending'
  ▼
Mahasiswa & admin dapat melihat hasil penempatan di dashboard
```

---

## 3. API Pihak Ketiga yang Digunakan

### Nominatim (OpenStreetMap Geocoding)

**Digunakan untuk:** Mengonversi teks alamat → koordinat (latitude/longitude).

| Detail | Nilai |
|--------|-------|
| Endpoint | `https://nominatim.openstreetmap.org/search` |
| Method | `GET` |
| Autentikasi | Tidak diperlukan (gratis, open-source) |
| Rate Limit | 1 request/detik (kebijakan OSM) |
| Format Response | JSON |

**Contoh request:**
```
GET https://nominatim.openstreetmap.org/search
  ?q=Jl+Merdeka+Jakarta+Pusat
  &format=json
  &limit=5
  &countrycodes=id
  &addressdetails=1
```

**Contoh response (dipangkas):**
```json
[
  {
    "place_id": 123456,
    "display_name": "Jalan Merdeka, Gambir, Jakarta Pusat, DKI Jakarta",
    "lat": "-6.1751",
    "lon": "106.8650",
    "type": "residential"
  }
]
```

**Controller yang menangani:** `app/Http/Controllers/GeocodeController.php`

```php
// Meneruskan query ke Nominatim dan mengembalikan hasilnya ke frontend
public function search(Request $request): JsonResponse
{
    $q = $request->validate(['q' => 'required|string|min:3'])['q'];

    $response = Http::withHeaders([
        'User-Agent' => 'KPM-PPL-Manager/1.0',
    ])->get('https://nominatim.openstreetmap.org/search', [
        'q'            => $q,
        'format'       => 'json',
        'limit'        => 5,
        'countrycodes' => 'id',
        'addressdetails' => 1,
    ]);

    return response()->json(['results' => $response->json()]);
}
```

**Tidak ada API key yang diperlukan.** Cukup sertakan `User-Agent` yang jelas agar tidak diblokir oleh server OSM.

### Geolocation API (Browser)

**Digunakan untuk:** Mendapatkan koordinat GPS langsung dari browser mahasiswa (alternatif dari pencarian teks).

```javascript
navigator.geolocation.getCurrentPosition(position => {
    // position.coords.latitude
    // position.coords.longitude
});
```

Tidak memerlukan API key. Berjalan di sisi client (JavaScript), bukan server.

---

## 4. Skema Database

### Tabel `schools` — Menyimpan Desa (KPM) & Sekolah (PPL)

```sql
CREATE TABLE schools (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,           -- "Desa Cempaka Putih" atau "SMAN 03 Jakarta"
    jenjang         TEXT,                       -- SD/SMP/SMA/SMK/MI/MTs/MA (PPL) atau Desa/Kelurahan (KPM)
    address         TEXT    NOT NULL,
    latitude        DECIMAL(10,7) NOT NULL,
    longitude       DECIMAL(10,7) NOT NULL,
    program         TEXT    NOT NULL DEFAULT 'BOTH', -- 'KPM' | 'PPL' | 'BOTH'
    kuota_kpm       INTEGER NOT NULL DEFAULT 0,  -- max mahasiswa KPM (0 jika PPL only)
    kuota_ppl       INTEGER NOT NULL DEFAULT 0,  -- max mahasiswa PPL (0 jika KPM only)
    contact_person  TEXT,
    phone           TEXT,
    is_active       BOOLEAN NOT NULL DEFAULT 1,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

**Kolom kunci untuk fitur lokasi otomatis:**

| Kolom | Peran |
|-------|-------|
| `latitude`, `longitude` | Titik koordinat lokasi — digunakan Haversine untuk menghitung jarak ke domisili mahasiswa |
| `program` | Menentukan apakah lokasi ini untuk `KPM` (desa), `PPL` (sekolah), atau `BOTH` |
| `kuota_kpm`, `kuota_ppl` | Batas mahasiswa per lokasi per program — sistem tidak akan menetapkan ke lokasi yang sudah penuh |
| `is_active` | Hanya lokasi aktif yang dipertimbangkan sistem |

### Tabel `mahasiswa_profiles` — Menyimpan Domisili Mahasiswa

```sql
CREATE TABLE mahasiswa_profiles (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL,
    nim             TEXT    NOT NULL,
    address         TEXT    NOT NULL,       -- Teks alamat lengkap
    latitude        DECIMAL(10,7) NOT NULL, -- Koordinat domisili — titik acuan jarak
    longitude       DECIMAL(10,7) NOT NULL,
    ...
);
```

**Kolom kunci:** `latitude` dan `longitude` adalah titik asal perhitungan jarak ke semua desa/sekolah.

### Tabel `registrations` — Hasil Penempatan

```sql
CREATE TABLE registrations (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    mahasiswa_profile_id  INTEGER NOT NULL REFERENCES mahasiswa_profiles(id),
    school_id             INTEGER NOT NULL REFERENCES schools(id),
    gelombang_id          INTEGER REFERENCES gelombang(id),
    program               TEXT    NOT NULL,  -- 'KPM' atau 'PPL'
    distance_km           DECIMAL(8,3),      -- Jarak domisili ke lokasi saat penetapan
    status                TEXT    NOT NULL DEFAULT 'pending',
    note                  TEXT,
    confirmed_at          TIMESTAMP,
    created_at            TIMESTAMP,
    updated_at            TIMESTAMP
);
```

### Tabel `gelombang` — Periode Pendaftaran

```sql
CREATE TABLE gelombang (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    program         TEXT    NOT NULL,  -- 'KPM' atau 'PPL'
    nomor           INTEGER NOT NULL,  -- Nomor urut gelombang
    tahun_akademik  TEXT    NOT NULL,  -- Contoh: "2024/2025"
    tanggal_buka    DATE,
    tanggal_tutup   DATE,
    is_active       BOOLEAN NOT NULL DEFAULT 0,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

Sistem hanya menetapkan lokasi jika ada gelombang **aktif** (`is_active = 1`) **dan terbuka** (`tanggal_buka ≤ hari ini ≤ tanggal_tutup`) untuk program yang bersangkutan.

### Tabel `settings` — Konfigurasi Global

```sql
CREATE TABLE settings (
    key   TEXT PRIMARY KEY,
    value TEXT
);
```

| Key | Nilai Default | Keterangan |
|-----|--------------|------------|
| `max_radius_km` | `10` | Radius pencarian Kondisi 1 (km) |
| `institution_name` | `Kampus Pendidikan` | Nama institusi |

---

## 5. Sisi Mahasiswa — Pemilihan Lokasi

Mahasiswa **tidak dapat** memilih desa atau sekolah secara manual. Yang dapat dilakukan mahasiswa hanyalah **memasukkan koordinat domisili** dengan akurat — sistem yang kemudian menetapkan lokasi terbaik.

### Cara Mahasiswa Mengisi Koordinat Domisili

Mahasiswa memiliki dua cara di halaman profil:

#### A. Pencarian Alamat Teks (via Nominatim)

1. Mahasiswa mengetik alamat lengkap di kolom "Alamat Tempat Tinggal"
2. Klik tombol **"Cari Koordinat (OpenStreetMap)"**
3. Sistem mengirim request ke `GeocodeController` → diteruskan ke Nominatim
4. Hasil pencarian muncul sebagai daftar — mahasiswa klik untuk memilih
5. Kolom `latitude` dan `longitude` terisi otomatis

```javascript
// Alur di frontend (resources/views/mahasiswa/dashboard.blade.php)
async function modalSearchAddress() {
    const q = document.getElementById('modal-address').value.trim();

    const r = await fetch(`/geocode?q=${encodeURIComponent(q)}`, {
        headers: { 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
    });
    const data = await r.json();

    // Tampilkan hasil → mahasiswa klik → isi lat/lng
}
```

#### B. GPS Browser

1. Mahasiswa klik **"Gunakan lokasi saya"**
2. Browser meminta izin akses GPS
3. Koordinat diisi otomatis dari `navigator.geolocation.getCurrentPosition()`

```javascript
navigator.geolocation.getCurrentPosition(p => {
    document.getElementById('modal-lat').value = p.coords.latitude.toFixed(7);
    document.getElementById('modal-lng').value = p.coords.longitude.toFixed(7);
});
```

### Kapan Lokasi Dapat Diperbarui

- Koordinat **hanya dapat diubah** saat status profil masih `pending`
- Setelah admin menyetujui (`approved`), tombol "Perbarui Lokasi Domisili" disembunyikan
- Ini mencegah manipulasi koordinat setelah penempatan ditetapkan

---

## 6. Sisi Admin — Input Lokasi Sekolah

Admin mendaftarkan desa (untuk KPM) dan sekolah (untuk PPL) melalui menu **Lokasi** di panel admin.

### Membedakan Desa vs Sekolah

Saat menambah lokasi, admin memilih **Program**:

| Pilihan Program | Tipe Lokasi | Kuota yang Diisi |
|----------------|-------------|-----------------|
| `KPM saja (Desa)` | Desa / Kelurahan | Kuota KPM saja |
| `PPL saja (Sekolah)` | Sekolah | Kuota PPL saja |
| `KPM & PPL` | Sekolah yang juga menerima KPM | Kuota KPM + PPL |

Form secara dinamis menyesuaikan label dan field yang tampil sesuai pilihan program (menggunakan JavaScript `updateLabels()`).

### Cara Admin Mengisi Koordinat Lokasi

Sama seperti mahasiswa — admin dapat menggunakan:
- **"Cari Koordinat via Alamat"** → Nominatim
- **"Gunakan Lokasi GPS Saya"** → Geolocation API

Koordinat ini adalah titik tujuan yang akan diukur jaraknya dari domisili setiap mahasiswa saat penetapan.

### Kuota Lokasi

Setiap lokasi memiliki kuota per program:
- `kuota_kpm`: maksimum mahasiswa KPM yang dapat ditempatkan di lokasi ini
- `kuota_ppl`: maksimum mahasiswa PPL yang dapat ditempatkan di lokasi ini

Sistem menghitung sisa kuota secara real-time:

```php
// app/Models/School.php
public function availableSlots(string $program): int
{
    $kuota = $program === 'KPM' ? $this->kuota_kpm : $this->kuota_ppl;
    $taken = $this->registrations()
        ->where('program', $program)
        ->whereIn('status', ['pending', 'approved'])
        ->count();

    return max(0, $kuota - $taken);
}
```

> Penempatan dengan status `pending` **sudah dihitung** sebagai slot terpakai — mencegah double-booking saat admin belum menyetujui registrasi.

---

## 7. Algoritma Haversine (Perhitungan Jarak)

Semua perhitungan jarak menggunakan **formula Haversine** — formula trigonometri bola yang memperhitungkan kelengkungan bumi, menghasilkan jarak garis lurus (great-circle distance) dalam kilometer.

### Implementasi

**File:** `app/Support/Geo.php`

```php
class Geo
{
    public static function distanceKm(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $earthRadius = 6371.0; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2
           + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // hasil dalam km
    }
}
```

### Penjelasan Langkah-langkah

```
1. Konversi derajat → radian (semua koordinat)
   lat_rad = lat_derajat × (π / 180)

2. Hitung selisih koordinat
   Δlat = lat2_rad − lat1_rad
   Δlon = lon2_rad − lon1_rad

3. Formula Haversine
   a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)
   c = 2 × atan2(√a, √(1−a))

4. Jarak = R × c
   R = 6371 km (jari-jari rata-rata bumi)
```

### Contoh Perhitungan

```
Domisili mahasiswa : -6.2295° LS, 106.8543° BT (Tebet, Jakarta Selatan)
Desa KPM           : -6.2349° LS, 106.9896° BT (Bekasi)

Δlat = -6.2349 − (-6.2295) = -0.0054° → rad = -0.0000942
Δlon = 106.9896 − 106.8543 = 0.1353°  → rad =  0.002362

a = sin²(-0.0000942/2) + cos(-0.1087) × cos(-0.1088) × sin²(0.002362/2)
  = 0.00000000222 + 0.9941 × 0.9941 × 0.000001385
  = 0.00000137

c = 2 × atan2(√0.00000137, √0.99999863)
  = 2 × 0.001172
  = 0.002344 radian

Jarak = 6371 × 0.002344 ≈ 14.93 km
```

### Akurasi

Formula Haversine akurat hingga ~0.5% untuk jarak di bawah 100 km. Untuk cakupan satu kota atau kabupaten, presisinya lebih dari cukup. Untuk jarak sangat jauh (>1000 km) disarankan menggunakan formula Vincenty, namun ini di luar kebutuhan sistem penempatan lokal.

---

## 8. Logika Filter Radius & Rekomendasi

### Kondisi 1 — Nearest Within Radius

**File:** `AutoAssignService::findNearestWithinRadius()`

```php
private function findNearestWithinRadius(
    MahasiswaProfile $profile,
    string $program,
    float $radius
): ?School {
    return School::query()
        ->where('is_active', true)
        ->where(function ($q) use ($program) {
            // Lokasi yang menerima program ini
            $q->where('program', $program)->orWhere('program', 'BOTH');
        })
        ->get()
        ->map(function (School $s) use ($profile) {
            // Hitung jarak ke setiap lokasi
            $s->distance = Geo::distanceKm(
                (float) $profile->latitude,
                (float) $profile->longitude,
                (float) $s->latitude,
                (float) $s->longitude,
            );
            return $s;
        })
        ->filter(fn (School $s) =>
            $s->distance <= $radius          // dalam radius
            && $s->availableSlots($program) > 0  // ada kuota
        )
        ->sortBy('distance')
        ->first(); // lokasi terdekat yang memenuhi syarat
}
```

**Diagram keputusan Kondisi 1:**

```
Untuk setiap lokasi aktif yang menerima program X:
  ┌─ Hitung jarak Haversine (domisili → lokasi)
  ├─ Apakah jarak ≤ max_radius_km? ──Tidak──► Skip
  └─ Apakah availableSlots > 0?   ──Tidak──► Skip
       │
       Ya → Masukkan ke daftar kandidat
       
Urutkan kandidat dari terdekat → pilih yang pertama
```

### Kondisi 2 — Proximity-Based Assignment

Dipicu ketika Kondisi 1 tidak menghasilkan lokasi. Tiga sub-skenario:

#### Skenario A: Hanya 1 Program Belum Terpenuhi

```
Jika program lain (anchor) sudah punya lokasi:
  → Cari lokasi program ini yang terdekat dari anchor

Jika program lain belum punya lokasi:
  → Cari lokasi program ini yang terdekat dari domisili mahasiswa
```

#### Skenario B: Kedua Program Belum Terpenuhi

```
Iterasi semua kombinasi (desa_kpm × sekolah_ppl):
  Untuk setiap pasangan (desa_i, sekolah_j):
    dist = Haversine(desa_i.lat, desa_i.lng, sekolah_j.lat, sekolah_j.lng)
    
  Pilih pasangan dengan dist terkecil
  → Tetapkan desa_i ke KPM, sekolah_j ke PPL
```

**Tujuan Skenario B:** Meminimalkan jarak tempuh mahasiswa **antar** lokasi KPM dan PPL, agar keduanya berdekatan meski mungkin jauh dari domisili.

#### Kompleksitas Waktu

| Skenario | Kompleksitas |
|----------|-------------|
| Kondisi 1 | O(n) — satu iterasi semua lokasi per program |
| Kondisi 2A | O(n) — satu iterasi lokasi program yang belum |
| Kondisi 2B | O(k × p) — k = jumlah desa KPM, p = jumlah sekolah PPL |

Untuk jumlah lokasi ratusan hingga ribuan, performa masih sangat baik karena semuanya berjalan in-memory setelah query database.

### Konfigurasi Radius

Radius default (10 km) dapat diubah di panel admin → menu **Pengaturan**.

```php
// Dibaca dari database saat runtime
$radius = (float) Setting::get('max_radius_km', 10);
```

**Panduan nilai radius:**

| Konteks | Radius Disarankan |
|---------|-----------------|
| Kota padat (Jakarta, Surabaya) | 5–10 km |
| Kota menengah | 10–20 km |
| Kabupaten / pedesaan | 20–50 km |

Jika radius terlalu kecil → banyak mahasiswa masuk Kondisi 2.  
Jika radius terlalu besar → mahasiswa yang dekat dan jauh mendapat lokasi yang sama, mengurangi keadilan.

---

## 9. Daftar File Penting

| File | Fungsi |
|------|--------|
| `app/Services/AutoAssignService.php` | **Inti** — seluruh logika penetapan lokasi KPM & PPL |
| `app/Support/Geo.php` | Formula Haversine untuk menghitung jarak (km) |
| `app/Models/School.php` | Model lokasi (desa/sekolah); `availableSlots()`, `acceptsProgram()`, `locationType()` |
| `app/Models/MahasiswaProfile.php` | Menyimpan `latitude`/`longitude` domisili mahasiswa |
| `app/Models/Registration.php` | Hasil penempatan; menyimpan `distance_km` dan `gelombang_id` |
| `app/Models/Gelombang.php` | Periode pendaftaran; `activeFor()`, `isOpen()` |
| `app/Models/Setting.php` | Konfigurasi `max_radius_km` |
| `app/Http/Controllers/GeocodeController.php` | Proxy ke Nominatim OSM untuk geocoding alamat |
| `app/Http/Controllers/Admin/MahasiswaManagementController.php` | Memanggil `AutoAssignService::assign()` saat admin approve |
| `app/Http/Controllers/Admin/SchoolController.php` | CRUD desa & sekolah oleh admin |
| `database/migrations/..._create_schools_table.php` | Skema tabel `schools` |
| `database/migrations/..._create_registrations_table.php` | Skema tabel `registrations` |
| `database/migrations/..._create_gelombang_table.php` | Skema tabel `gelombang` |
| `resources/views/mahasiswa/dashboard.blade.php` | Dashboard mahasiswa — menampilkan hasil penempatan & form lokasi |
| `resources/views/admin/schools/` | Halaman CRUD lokasi (desa/sekolah) untuk admin |

---

## 10. Cara Menerapkan di Project Lain

Berikut langkah-langkah untuk mengadaptasi fitur ini ke project Laravel lain:

### Langkah 1 — Salin Kelas Inti

```
app/Support/Geo.php              ← Formula Haversine, tidak ada dependensi
app/Services/AutoAssignService.php ← Sesuaikan dengan model Anda
```

### Langkah 2 — Struktur Tabel Minimal

Tabel "lokasi" (desa/sekolah) membutuhkan minimal:
```sql
latitude   DECIMAL(10,7) NOT NULL
longitude  DECIMAL(10,7) NOT NULL
kuota      INTEGER       NOT NULL DEFAULT 0
is_active  BOOLEAN       NOT NULL DEFAULT 1
```

Tabel "pengguna/mahasiswa" membutuhkan:
```sql
latitude   DECIMAL(10,7) NOT NULL
longitude  DECIMAL(10,7) NOT NULL
```

### Langkah 3 — Geocoding Route

Tambahkan route dan controller untuk proxy ke Nominatim:

```php
// routes/web.php
Route::get('/geocode', [GeocodeController::class, 'search'])->name('geocode');
```

```php
// app/Http/Controllers/GeocodeController.php
public function search(Request $request): JsonResponse
{
    $q = $request->validate(['q' => 'required|string|min:3'])['q'];
    $response = Http::withHeaders(['User-Agent' => 'NamaApp/1.0'])
        ->get('https://nominatim.openstreetmap.org/search', [
            'q' => $q, 'format' => 'json', 'limit' => 5,
            'countrycodes' => 'id', 'addressdetails' => 1,
        ]);
    return response()->json(['results' => $response->json()]);
}
```

### Langkah 4 — Form Input Koordinat di Frontend

Tambahkan dua input tersembunyi (`lat`, `lng`) dan dua tombol (GPS + cari alamat) ke form apapun yang memerlukan koordinat. Gunakan pola JavaScript dari `resources/views/mahasiswa/dashboard.blade.php` sebagai referensi.

### Langkah 5 — Panggil AutoAssignService

Panggil service saat event tertentu (misal: approval, pendaftaran):

```php
use App\Services\AutoAssignService;

// Di controller
$service = new AutoAssignService();
$results = $service->assign($mahasiswaProfile);

// $results['KPM']['school'] → School|null
// $results['PPL']['school'] → School|null
// $results['KPM']['method'] → 'domisili' | 'proximity' | null
// $results['KPM']['reason'] → 'no_active_wave' (jika gelombang tidak aktif)
```

### Langkah 6 — Konfigurasi Radius

Simpan `max_radius_km` di tabel `settings` atau file `.env`, lalu baca di `AutoAssignService`:

```php
$radius = (float) Setting::get('max_radius_km', 10);
// atau
$radius = (float) config('app.max_radius_km', 10);
```

### Dependensi Composer

Tidak ada dependensi tambahan yang diperlukan — semua logika menggunakan PHP murni dan `illuminate/http` (sudah ada di Laravel).

---

*Dokumentasi ini mencakup implementasi per **Mei 2026**. Perubahan pada skema database atau logika service perlu diperbarui sesuai.*
