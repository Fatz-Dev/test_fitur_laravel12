# Fitur Pemilihan Lokasi Otomatis — KPM & PPL

> Dokumentasi teknis sistem penetapan lokasi KPM (Desa) dan PPL (Sekolah) secara otomatis berdasarkan domisili mahasiswa dan pilihan program.

---

## Daftar Isi

1. [Ringkasan Fitur](#1-ringkasan-fitur)
2. [Arsitektur & Alur Sistem](#2-arsitektur--alur-sistem)
3. [API Pihak Ketiga yang Digunakan](#3-api-pihak-ketiga-yang-digunakan)
4. [Skema Database](#4-skema-database)
5. [Sisi Mahasiswa — Pemilihan Lokasi](#5-sisi-mahasiswa--pemilihan-lokasi)
6. [Sisi Admin — Input Lokasi Desa & Sekolah](#6-sisi-admin--input-lokasi-desa--sekolah)
7. [Algoritma Haversine (Perhitungan Jarak)](#7-algoritma-haversine-perhitungan-jarak)
8. [Logika Filter Radius & Rekomendasi](#8-logika-filter-radius--rekomendasi)
9. [Daftar File Penting](#9-daftar-file-penting)
10. [Cara Menerapkan di Project Lain](#10-cara-menerapkan-di-project-lain)

---

## 1. Ringkasan Fitur

Sistem ini menetapkan lokasi penempatan mahasiswa **secara otomatis** — tanpa mahasiswa memilih lokasi sendiri. Mahasiswa hanya memilih **jenis program** dan **mengisi koordinat domisili**. Penempatan dipicu ketika admin menyetujui profil mahasiswa.

### Tiga Pilihan Program

| Kode | Nama | Lokasi Tujuan | Algoritma |
|------|------|--------------|-----------|
| **KPM** | Kuliah Pengabdian Masyarakat | 1 Desa / Kelurahan | Kondisi 1 → Kondisi 2 |
| **PPL** | Praktik Pengalaman Lapangan | 1 Sekolah (SD–MA/SMK) | Kondisi 1 → Kondisi 2 |
| **PKPPM** | KPM + PPL sekaligus | 1 Desa + 1 Sekolah | Kondisi 2 langsung |

### Dua Kondisi Penetapan

- **Kondisi 1 — Berbasis Radius** *(untuk KPM saja atau PPL saja)*: Sistem mencari desa/sekolah terdekat dari koordinat domisili dalam radius yang dapat dikonfigurasi (default 10 km). Jika ada lokasi dengan kuota tersedia, langsung ditetapkan.

- **Kondisi 2 — Berbasis Kedekatan Antar-Lokasi** *(selalu digunakan untuk PKPPM; fallback untuk KPM/PPL jika Kondisi 1 kosong)*: Sistem mencari pasangan desa KPM + sekolah PPL yang paling dekat **satu sama lain**, meminimalkan jarak tempuh mahasiswa antar dua lokasi penempatan.

> **Mengapa PKPPM langsung ke Kondisi 2?** Karena untuk mahasiswa yang mengikuti dua program sekaligus, lebih penting bahwa desa dan sekolah yang ditetapkan *berdekatan satu sama lain* daripada masing-masing dekat ke domisili. Ini mengurangi beban perjalanan antar lokasi selama program berlangsung.

---

## 2. Arsitektur & Alur Sistem

```
Mahasiswa mengisi formulir pendaftaran
  │
  ├─ Pilih Program: KPM | PPL | PKPPM
  ├─ Isi alamat domisili + koordinat (via peta Leaflet / geocoding / GPS)
  └─ Upload 4 berkas persyaratan
  │
  ▼
Admin mereview profil mahasiswa
  │
  ▼
Admin klik "Setujui & Tetapkan Penempatan"
  │
  ▼
MahasiswaManagementController@approve()
  │  → profile->status = 'approved'
  │  → memanggil AutoAssignService::assign($profile)
  │
  ▼
AutoAssignService::assign(MahasiswaProfile $profile)
  │
  ├─ Baca profile->program_choice → ['KPM'], ['PPL'], atau ['KPM','PPL']
  ├─ Baca profile->programsToAssign() → daftar program yang perlu ditetapkan
  │
  ├─ Cek gelombang aktif per program:
  │    Gelombang::activeFor('KPM') → is_active=true & isOpen() = true?
  │    Gelombang::activeFor('PPL') → is_active=true & isOpen() = true?
  │    Jika tidak ada → skip program tersebut (reason: no_active_wave)
  │
  ├─ [PKPPM] → langsung Kondisi 2
  │    Iterasi semua kombinasi (desa_kpm × sekolah_ppl)
  │    Pilih pasangan dengan jarak antar-lokasi terkecil
  │
  └─ [KPM atau PPL saja]
       ├─ Kondisi 1: findNearestWithinRadius()
       │    Haversine(domisili, lokasi) ≤ radius && kuota > 0 → tetapkan
       │
       └─ Kondisi 2 (fallback): assignByProximity()
            Jika tidak ada dalam radius → cari lokasi paling dekat
            (dari anchor lokasi lain jika ada, atau dari domisili)
  │
  ▼
Registration::create() → simpan ke tabel registrations
  • mahasiswa_profile_id, school_id, gelombang_id
  • program ('KPM' atau 'PPL'), distance_km, status='pending'
  │
  ▼
Admin & mahasiswa melihat hasil penempatan di dashboard masing-masing
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
    "display_name": "Jalan Merdeka, Gambir, Jakarta Pusat, DKI Jakarta",
    "lat": "-6.1751",
    "lon": "106.8650"
  }
]
```

**Controller yang menangani:** `app/Http/Controllers/GeocodeController.php`

```php
public function search(Request $request): JsonResponse
{
    $q = $request->validate(['q' => 'required|string|min:3'])['q'];

    $response = Http::withHeaders(['User-Agent' => 'KPM-PPL-Manager/1.0'])
        ->get('https://nominatim.openstreetmap.org/search', [
            'q' => $q, 'format' => 'json', 'limit' => 5,
            'countrycodes' => 'id', 'addressdetails' => 1,
        ]);

    return response()->json(['results' => $response->json()]);
}
```

### Leaflet.js + OpenStreetMap Tiles

**Digunakan untuk:** Peta interaktif di halaman form profil mahasiswa, form lokasi admin, dan halaman detail mahasiswa.

| Detail | Nilai |
|--------|-------|
| Library | Leaflet.js 1.9.4 |
| CDN | `https://unpkg.com/leaflet@1.9.4/dist/leaflet.js` |
| Tile Provider | OpenStreetMap (`{s}.tile.openstreetmap.org`) |
| API Key | Tidak diperlukan |

Fungsionalitas peta:
- **Form lokasi admin:** klik peta → mengisi lat/lng otomatis; drag marker untuk perbaikan presisi
- **Form profil mahasiswa:** klik peta → mengisi koordinat domisili
- **Detail mahasiswa (admin):** marker biru (domisili), amber (desa KPM), biru langit (sekolah PPL), dengan garis putus-putus

### Geolocation API (Browser)

**Digunakan untuk:** Koordinat GPS real-time dari perangkat pengguna (alternatif pencarian teks).

```javascript
navigator.geolocation.getCurrentPosition(position => {
    // position.coords.latitude / longitude
});
```

Tidak memerlukan API key. Berjalan di sisi client.

---

## 4. Skema Database

### Tabel `schools` — Desa (KPM) & Sekolah (PPL)

```sql
CREATE TABLE schools (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,     -- "Desa Cempaka Putih" atau "SMAN 03 Jakarta"
    jenjang         TEXT,                 -- SD/SMP/.../MA (PPL) atau Desa/Kelurahan (KPM)
    address         TEXT    NOT NULL,
    latitude        DECIMAL(10,7) NOT NULL,
    longitude       DECIMAL(10,7) NOT NULL,
    program         TEXT    NOT NULL DEFAULT 'BOTH',  -- 'KPM' | 'PPL' | 'BOTH'
    kuota_kpm       INTEGER NOT NULL DEFAULT 0,
    kuota_ppl       INTEGER NOT NULL DEFAULT 0,
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
| `latitude`, `longitude` | Titik koordinat lokasi — dipakai Haversine untuk menghitung jarak ke domisili mahasiswa |
| `program` | Menentukan apakah lokasi ini untuk `KPM` (desa), `PPL` (sekolah), atau `BOTH` |
| `kuota_kpm`, `kuota_ppl` | Batas mahasiswa per lokasi — lokasi penuh tidak akan dipilih |
| `is_active` | Hanya lokasi aktif yang dipertimbangkan sistem |

### Tabel `mahasiswa_profiles` — Profil & Pilihan Program Mahasiswa

```sql
CREATE TABLE mahasiswa_profiles (
    id                   INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id              INTEGER NOT NULL,
    nim                  TEXT    NOT NULL UNIQUE,
    program_choice       TEXT    NOT NULL DEFAULT 'PKPPM', -- 'KPM' | 'PPL' | 'PKPPM'
    phone                TEXT,
    address              TEXT    NOT NULL,
    latitude             DECIMAL(10,7) NOT NULL,   -- titik asal perhitungan jarak
    longitude            DECIMAL(10,7) NOT NULL,
    microteaching_grade  TEXT    NOT NULL,
    transkrip_path       TEXT,
    ktm_path             TEXT,
    surat_pengantar_path TEXT,
    pas_foto_path        TEXT,
    status               TEXT    NOT NULL DEFAULT 'pending',  -- pending|approved|rejected
    admin_note           TEXT,
    reviewed_at          TIMESTAMP,
    created_at           TIMESTAMP,
    updated_at           TIMESTAMP
);
```

**Kolom `program_choice`** menentukan alur penetapan di `AutoAssignService`:

| Nilai | Program Yang Ditetapkan | Algoritma |
|-------|------------------------|-----------|
| `KPM` | Hanya KPM (desa) | Kondisi 1 → Kondisi 2 |
| `PPL` | Hanya PPL (sekolah) | Kondisi 1 → Kondisi 2 |
| `PKPPM` | KPM + PPL keduanya | Kondisi 2 langsung (pasangan berdekatan) |

### Tabel `registrations` — Hasil Penempatan

```sql
CREATE TABLE registrations (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    mahasiswa_profile_id  INTEGER NOT NULL REFERENCES mahasiswa_profiles(id),
    school_id             INTEGER NOT NULL REFERENCES schools(id),
    gelombang_id          INTEGER REFERENCES gelombang(id),
    program               TEXT    NOT NULL,           -- 'KPM' atau 'PPL'
    distance_km           DECIMAL(8,3),               -- jarak domisili → lokasi
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
    program         TEXT    NOT NULL,   -- 'KPM' atau 'PPL'
    nomor           INTEGER NOT NULL,
    tahun_akademik  TEXT    NOT NULL,
    tanggal_buka    DATE,
    tanggal_tutup   DATE,
    is_active       BOOLEAN NOT NULL DEFAULT 0,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

Penempatan hanya terjadi jika gelombang **aktif** (`is_active=1`) **dan terbuka** (`tanggal_buka ≤ hari ini ≤ tanggal_tutup`) per program.

### Tabel `settings` — Konfigurasi

| Key | Default | Keterangan |
|-----|---------|------------|
| `max_radius_km` | `10` | Radius pencarian Kondisi 1 (km) |
| `institution_name` | `Kampus Pendidikan` | Nama institusi |

---

## 5. Sisi Mahasiswa — Pemilihan Lokasi

### Langkah 1 — Pilih Program

Di halaman `mahasiswa/profile/create`, mahasiswa memilih satu dari tiga program melalui kartu pilihan interaktif:

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐
│  🏘️ KPM         │  │  🏫 PPL         │  │  🏘️🏫 PKPPM         │
│  Desa saja      │  │  Sekolah saja   │  │  Desa + Sekolah     │
│                 │  │                 │  │                     │
│  1 penempatan   │  │  1 penempatan   │  │  2 penempatan       │
│  Kondisi 1→2    │  │  Kondisi 1→2    │  │  Kondisi 2 langsung │
└─────────────────┘  └─────────────────┘  └─────────────────────┘
```

Ketika PKPPM dipilih, muncul info box penjelasan algoritma kedekatan antar-lokasi.

### Langkah 2 — Isi Koordinat Domisili

Tiga cara mengisi koordinat:

**A. Klik Peta (Leaflet)**
```
Peta interaktif muncul di sisi kanan form.
Klik di mana saja → marker muncul → lat/lng terisi otomatis.
Drag marker untuk presisi lebih baik.
```

**B. Pencarian Alamat (Nominatim)**
```javascript
// Ketik alamat → klik "Cari dari Alamat"
// → request ke /geocode → Nominatim → tampil daftar hasil
// → klik hasil → marker pindah ke lokasi tersebut
```

**C. GPS Browser**
```javascript
navigator.geolocation.getCurrentPosition(p => {
    setCoords(p.coords.latitude, p.coords.longitude);
});
```

### Kapan Koordinat Dapat Diubah

- Hanya saat status profil masih `pending`
- Setelah admin menyetujui, tombol "Perbarui Lokasi Domisili" disembunyikan
- Mencegah manipulasi koordinat setelah penempatan ditetapkan

### Upload Berkas

Mahasiswa mengunggah 4 berkas sekaligus melalui satu drop zone:
- Drop zone utama → pilih hingga 4 file sekaligus → JS mengisi slot secara urut
- Atau pilih file individual per slot
- 4 slot: Transkrip, KTM, Surat Pengantar, Pas Foto
- Backend menerima 4 field terpisah (`transkrip`, `ktm`, `surat_pengantar`, `pas_foto`)

---

## 6. Sisi Admin — Input Lokasi Desa & Sekolah

Admin mendaftarkan desa (KPM) dan sekolah (PPL) via menu **Lokasi**.

### Membedakan Desa vs Sekolah

| Program | Tipe | Jenjang |
|---------|------|---------|
| `KPM` | Desa | Desa / Kelurahan / Kecamatan |
| `PPL` | Sekolah | SD / SMP / SMA / SMK / MI / MTs / MA |
| `BOTH` | Keduanya | Jenjang sekolah (juga menerima KPM) |

Form menyesuaikan label dan pilihan jenjang secara dinamis (JavaScript `updateLabels()`).

### Peta di Form Lokasi

Peta Leaflet interaktif di sisi kanan form:
- Klik peta → mengisi kolom Latitude & Longitude
- Drag marker → memperbarui koordinat
- Tombol GPS / Cari Alamat → memindahkan marker ke posisi baru

### Kuota Lokasi

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

> Slot `pending` sudah dihitung sebagai terpakai — mencegah overbooking.

### Peta di Halaman Detail Mahasiswa

Menampilkan 3 jenis marker sekaligus:
- 🔵 Biru — domisili mahasiswa
- 🟡 Amber — desa KPM
- 🔵 Biru Langit — sekolah PPL
- Garis putus-putus menghubungkan domisili ke masing-masing lokasi

---

## 7. Algoritma Haversine (Perhitungan Jarak)

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

        return $earthRadius * $c;
    }
}
```

### Formula Matematika

```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)
c = 2 × atan2(√a, √(1−a))
d = R × c          (R = 6371 km)
```

### Akurasi & Batas

- Akurat hingga ~0,5% untuk jarak di bawah 100 km
- Sangat cukup untuk cakupan kota/kabupaten
- Tidak memerlukan library tambahan — hanya PHP bawaan

---

## 8. Logika Filter Radius & Rekomendasi

### Kondisi 1 — Nearest Within Radius (KPM/PPL saja)

```php
private function findNearestWithinRadius(
    MahasiswaProfile $profile,
    string $program,
    float $radius
): ?School {
    return School::query()
        ->where('is_active', true)
        ->where(fn($q) => $q->where('program', $program)->orWhere('program', 'BOTH'))
        ->get()
        ->map(function (School $s) use ($profile) {
            $s->distance = Geo::distanceKm(
                $profile->latitude, $profile->longitude,
                $s->latitude, $s->longitude
            );
            return $s;
        })
        ->filter(fn($s) => $s->distance <= $radius && $s->availableSlots($program) > 0)
        ->sortBy('distance')
        ->first();
}
```

**Diagram keputusan:**
```
Untuk setiap lokasi aktif yang menerima program X:
  Hitung Haversine(domisili, lokasi) → distance
  Apakah distance ≤ max_radius_km?  ──Tidak──► Skip
  Apakah availableSlots > 0?         ──Tidak──► Skip
  → Masuk daftar kandidat

Urutkan ascending → ambil pertama (terdekat)
```

### Kondisi 2 — Proximity-Based (PKPPM atau fallback)

**Skenario A: 1 program belum terpenuhi**
```
Jika program lain sudah ada lokasi (anchor):
  → Cari lokasi program ini terdekat dari anchor

Jika tidak ada anchor:
  → Cari lokasi program ini terdekat dari domisili
```

**Skenario B: Kedua program belum terpenuhi (utama untuk PKPPM)**
```
Iterasi SEMUA kombinasi (desa_kpm × sekolah_ppl):
  dist = Haversine(desa_i, sekolah_j)
  
Pilih pasangan dengan dist terkecil
→ desa_i → KPM, sekolah_j → PPL
```

**Kompleksitas waktu:**

| Kondisi | Kompleksitas |
|---------|-------------|
| Kondisi 1 | O(n) |
| Kondisi 2A | O(n) |
| Kondisi 2B (PKPPM) | O(k × p) — k=jumlah desa, p=jumlah sekolah |

Seluruh komputasi berjalan in-memory setelah satu query database. Performanya sangat baik untuk ratusan hingga ribuan lokasi.

### Tabel Keputusan Lengkap

```
program_choice  gelombang aktif?   kuota ada?    hasil
─────────────────────────────────────────────────────────
KPM             Tidak              -             Tidak ditetapkan (no_active_wave)
KPM             Ya                 Ya (radius)   Kondisi 1: desa terdekat
KPM             Ya                 Tidak (radius) Kondisi 2A: desa terdekat (tanpa radius)
PPL             Tidak              -             Tidak ditetapkan (no_active_wave)
PPL             Ya                 Ya (radius)   Kondisi 1: sekolah terdekat
PPL             Ya                 Tidak (radius) Kondisi 2A: sekolah terdekat (tanpa radius)
PKPPM           KPM/PPL aktif      Ada keduanya  Kondisi 2B: pasangan desa+sekolah terdekat
PKPPM           Hanya KPM aktif    -             Hanya desa ditetapkan
PKPPM           Hanya PPL aktif    -             Hanya sekolah ditetapkan
```

---

## 9. Daftar File Penting

| File | Fungsi |
|------|--------|
| `app/Services/AutoAssignService.php` | **Inti** — logika penetapan KPM/PPL/PKPPM |
| `app/Support/Geo.php` | Formula Haversine |
| `app/Models/School.php` | Model lokasi; `availableSlots()`, `locationType()`, `labelFor()` |
| `app/Models/MahasiswaProfile.php` | Menyimpan `latitude/longitude` dan `program_choice`; `programsToAssign()` |
| `app/Models/Registration.php` | Hasil penempatan; menyimpan `distance_km`, `gelombang_id` |
| `app/Models/Gelombang.php` | Periode pendaftaran; `activeFor()`, `isOpen()` |
| `app/Models/Setting.php` | Konfigurasi `max_radius_km` |
| `app/Http/Controllers/GeocodeController.php` | Proxy ke Nominatim OSM |
| `app/Http/Controllers/Admin/MahasiswaManagementController.php` | Memanggil `AutoAssignService::assign()` saat approve |
| `app/Http/Controllers/Admin/SchoolController.php` | CRUD desa & sekolah |
| `app/Http/Controllers/MahasiswaController.php` | Menyimpan `program_choice` saat pendaftaran |
| `resources/views/mahasiswa/profile-create.blade.php` | Form pendaftaran: pilih program + peta + upload berkas |
| `resources/views/admin/schools/form.blade.php` | Form CRUD lokasi dengan peta Leaflet |
| `resources/views/admin/mahasiswa/show.blade.php` | Detail mahasiswa + peta penempatan |

---

## 10. Cara Menerapkan di Project Lain

### Langkah 1 — Salin Kelas Inti

```
app/Support/Geo.php                → Formula Haversine (tidak ada dependensi)
app/Services/AutoAssignService.php → Sesuaikan nama model
```

### Langkah 2 — Struktur Tabel Minimal

**Tabel "lokasi"** membutuhkan:
```sql
latitude   DECIMAL(10,7) NOT NULL
longitude  DECIMAL(10,7) NOT NULL
kuota      INTEGER       NOT NULL DEFAULT 0
is_active  BOOLEAN       NOT NULL DEFAULT 1
program    TEXT          NOT NULL  -- 'KPM' | 'PPL' | 'BOTH'
```

**Tabel "pengguna"** membutuhkan:
```sql
latitude        DECIMAL(10,7) NOT NULL
longitude       DECIMAL(10,7) NOT NULL
program_choice  TEXT NOT NULL DEFAULT 'PKPPM'  -- 'KPM' | 'PPL' | 'PKPPM'
```

### Langkah 3 — Geocoding Route

```php
// routes/web.php
Route::get('/geocode', [GeocodeController::class, 'search'])->name('geocode');
```

### Langkah 4 — Peta Leaflet (CDN, tanpa API key)

```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div id="map" style="height:400px;"></div>
<script>
const map = L.map('map').setView([-6.2, 106.8], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const marker = L.marker([-6.2, 106.8], { draggable: true }).addTo(map);
map.on('click', e => marker.setLatLng(e.latlng));
marker.on('dragend', e => {
    const ll = e.target.getLatLng();
    // simpan ke input form
});
</script>
```

### Langkah 5 — Pilihan Program di Form

```html
<input type="radio" name="program_choice" value="KPM">  KPM (Desa saja)
<input type="radio" name="program_choice" value="PPL">  PPL (Sekolah saja)
<input type="radio" name="program_choice" value="PKPPM"> PKPPM (Desa + Sekolah)
```

### Langkah 6 — Panggil AutoAssignService

```php
use App\Services\AutoAssignService;

$service = new AutoAssignService();
$results = $service->assign($mahasiswaProfile);

// $results['KPM']['school']  → School|null (desa yang ditetapkan)
// $results['PPL']['school']  → School|null (sekolah yang ditetapkan)
// $results['KPM']['method']  → 'domisili' | 'proximity' | null
// $results['KPM']['reason']  → 'no_active_wave' (jika gelombang tidak aktif)
```

### Langkah 7 — Konfigurasi Radius

```php
$radius = (float) Setting::get('max_radius_km', 10);
// atau: config('app.max_radius_km', 10)
```

**Panduan nilai:**

| Konteks | Radius Disarankan |
|---------|-----------------|
| Kota padat | 5–10 km |
| Kota menengah | 10–20 km |
| Kabupaten / pedesaan | 20–50 km |

### Dependensi Composer

Tidak ada dependensi tambahan. Semua logika menggunakan PHP murni + `illuminate/http` (sudah ada di Laravel).

---

*Dokumentasi ini mencakup implementasi per **Mei 2026**.*
