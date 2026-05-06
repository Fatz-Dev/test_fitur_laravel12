# Fitur Pemilihan Lokasi Otomatis (Auto-Assign)

> Dokumentasi teknis lengkap untuk pengembang. Mencakup arsitektur sistem, skema database, logika algoritma, edge cases, return value, dan panduan re-implementasi di project lain.

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Arsitektur & File yang Terlibat](#2-arsitektur--file-yang-terlibat)
3. [Skema Database](#3-skema-database)
4. [Konsep Dasar](#4-konsep-dasar)
5. [Pre-Condition: Gelombang Aktif](#5-pre-condition-gelombang-aktif)
6. [Algoritma Penetapan](#6-algoritma-penetapan)
7. [Edge Cases Lengkap](#7-edge-cases-lengkap)
8. [Return Value `assign()`](#8-return-value-assign)
9. [Kalkulasi Jarak — Formula Haversine](#9-kalkulasi-jarak--formula-haversine)
10. [Konfigurasi & Caching](#10-konfigurasi--caching)
11. [Cara Dipanggil — Entry Point](#11-cara-dipanggil--entry-point)
12. [Panduan Re-Implementasi di Project Lain](#12-panduan-re-implementasi-di-project-lain)
13. [Troubleshooting](#13-troubleshooting)

---

## 1. Gambaran Umum

SIPEP menetapkan lokasi KPM (desa) dan/atau PPL (sekolah) untuk mahasiswa **secara otomatis** saat admin menyetujui profil. Mahasiswa tidak memilih lokasi sendiri — sistem menentukannya berdasarkan:

- **Koordinat domisili** mahasiswa (latitude/longitude)
- **Program yang dipilih** (KPM, PPL, atau PKPPM)
- **Kuota tersedia** di setiap lokasi
- **Gelombang aktif** yang sedang berjalan
- **Radius maksimum** yang dikonfigurasi admin

Tujuan akhir: menempatkan mahasiswa di lokasi yang paling **dekat dari domisili** mereka (atau paling **dekat satu sama lain** untuk PKPPM), selama kuota masih tersedia.

---

## 2. Arsitektur & File yang Terlibat

```
app/
├── Services/
│   └── AutoAssignService.php        ← Logika utama (entry point: assign())
├── Support/
│   └── Geo.php                      ← Helper kalkulasi jarak Haversine
├── Models/
│   ├── School.php                   ← availableSlots(), acceptsProgram()
│   ├── MahasiswaProfile.php         ← programsToAssign(), isApproved()
│   ├── Registration.php             ← Menyimpan hasil penetapan
│   ├── Gelombang.php                ← activeFor(), isOpen()
│   └── Setting.php                  ← get('max_radius_km') + caching
└── Http/Controllers/Admin/
    └── MahasiswaManagementController.php  ← Memanggil AutoAssignService::assign()
```

**Dependency graph:**

```
MahasiswaManagementController
        │ inject
        ▼
AutoAssignService::assign()
        │ uses
        ├── Geo::distanceKm()
        ├── Setting::get('max_radius_km')
        ├── Gelombang::activeFor($program)
        ├── School::query() + availableSlots()
        └── Registration::create()
```

---

## 3. Skema Database

### Tabel `mahasiswa_profiles`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `nim` | varchar unique | Nomor induk mahasiswa |
| `address` | text | Alamat domisili (teks) |
| `latitude` | decimal(10,7) | Koordinat domisili ← **kritis untuk auto-assign** |
| `longitude` | decimal(10,7) | Koordinat domisili ← **kritis untuk auto-assign** |
| `program_choice` | enum | `'KPM'`, `'PPL'`, atau `'PKPPM'` ← menentukan algoritma |
| `microteaching_grade` | enum | A/B/C/D/E |
| `status` | enum | `'pending'`, `'approved'`, `'rejected'` |
| `reviewed_at` | timestamp | Saat admin approve/reject |

> **Catatan:** Auto-assign hanya berjalan saat `status` diubah ke `'approved'`. Tanpa `latitude`/`longitude` yang valid, penetapan akan menghasilkan jarak yang salah.

### Tabel `schools`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | |
| `name` | varchar | Nama lokasi |
| `jenjang` | varchar(50) | SD/SMP/SMA/SMK/MI/MTs/MA (khusus PPL) |
| `address` | text | Alamat lokasi |
| `latitude` | decimal(10,7) | Koordinat lokasi ← **kritis** |
| `longitude` | decimal(10,7) | Koordinat lokasi ← **kritis** |
| `program` | varchar | `'KPM'` atau `'PPL'` ← menentukan pool |
| `kuota_kpm` | unsigned int | Kapasitas mahasiswa KPM |
| `kuota_ppl` | unsigned int | Kapasitas mahasiswa PPL |
| `is_active` | boolean | Hanya sekolah aktif yang masuk pool |
| `supervisor_id` | FK → users | Dosen pembimbing (nullable) |

> **Penting:** Kolom `program` di `schools` menentukan apakah lokasi masuk **pool KPM** atau **pool PPL**. Satu lokasi hanya bisa untuk satu program. Tidak ada lokasi "keduanya" — mahasiswa PKPPM mendapat dua lokasi terpisah dari dua pool berbeda.

### Tabel `registrations`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | |
| `mahasiswa_profile_id` | FK | |
| `school_id` | FK | Lokasi yang ditetapkan |
| `gelombang_id` | FK → gelombang | Gelombang saat penetapan (nullable) |
| `program` | enum | `'KPM'` atau `'PPL'` |
| `distance_km` | decimal(8,3) | Jarak domisili mahasiswa ke lokasi (km) |
| `status` | enum | `'pending'`, `'approved'`, `'rejected'`, `'cancelled'` |
| `note` | text | Catatan admin |
| `confirmed_at` | timestamp | |

> **Unique constraint:** `(mahasiswa_profile_id, program)` — satu mahasiswa hanya bisa punya **satu** registration per program. Jika sudah ada pending/approved, sistem tidak akan membuat yang baru.

### Tabel `gelombang`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | |
| `program` | varchar | `'KPM'` atau `'PPL'` |
| `nomor` | int | Nomor gelombang (1, 2, 3, …) |
| `tahun_akademik` | varchar | Contoh: `'2024/2025'` |
| `tanggal_buka` | date | Mulai buka pendaftaran |
| `tanggal_tutup` | date | Tutup pendaftaran |
| `is_active` | boolean | Harus `true` agar bisa dipakai |

### Tabel `settings`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `key` | varchar | Nama konfigurasi |
| `value` | varchar | Nilai konfigurasi |

Kunci yang dipakai fitur ini:

| Key | Default | Keterangan |
|-----|---------|------------|
| `max_radius_km` | `10` | Radius pencarian Kondisi 1 (km) |

---

## 4. Konsep Dasar

### Program Mahasiswa

| `program_choice` | Artinya | Lokasi yang ditetapkan |
|-----------------|---------|----------------------|
| `'KPM'` | KPM saja | 1 registration (program=KPM) |
| `'PPL'` | PPL saja | 1 registration (program=PPL) |
| `'PKPPM'` | KPM + PPL | 2 registration (program=KPM + program=PPL) |

`programsToAssign()` di `MahasiswaProfile` mengembalikan array program yang perlu ditetapkan:

```php
// MahasiswaProfile.php
public function programsToAssign(): array
{
    return match ($this->program_choice) {
        'KPM'   => ['KPM'],
        'PPL'   => ['PPL'],
        default => ['KPM', 'PPL'],  // PKPPM
    };
}
```

### Kuota Lokasi

`availableSlots()` di `School` menghitung sisa kuota secara real-time:

```php
// School.php
public function availableSlots(string $program): int
{
    $kuota = $program === 'KPM' ? $this->kuota_kpm : $this->kuota_ppl;
    $taken = $this->registrations()
        ->where('program', $program)
        ->whereIn('status', ['pending', 'approved'])  // ← pending dihitung!
        ->count();

    return max(0, $kuota - $taken);
}
```

> **Penting:** Registration berstatus `'pending'` **sudah mengurangi kuota**. Ini mencegah overbooking saat banyak admin approve sekaligus.

---

## 5. Pre-Condition: Gelombang Aktif

Ini bagian yang **sering terlewat** namun sangat penting. Sebelum penetapan dilakukan, sistem memeriksa apakah ada **gelombang (wave) yang sedang aktif dan terbuka** untuk program tersebut.

```php
$gelombang = Gelombang::activeFor($program);
if (! $gelombang || ! $gelombang->isOpen()) {
    $results[$program] = [
        'school'  => null,
        'method'  => null,
        'reason'  => 'no_active_wave',   // ← penetapan dilewati
    ];
    continue;
}
```

### Syarat Gelombang Dianggap "Terbuka" (`isOpen()`)

Semua kondisi berikut harus terpenuhi:

| # | Kondisi |
|---|---------|
| 1 | `is_active = true` |
| 2 | Jika `tanggal_buka` diset: tanggal hari ini ≥ `tanggal_buka` |
| 3 | Jika `tanggal_tutup` diset: tanggal hari ini ≤ `tanggal_tutup` |

### Implikasi

- Jika **tidak ada gelombang KPM aktif**, mahasiswa KPM/PKPPM tidak mendapat penetapan KPM.
- Jika **gelombang sudah tutup** (tanggal_tutup terlewat), tidak ada penetapan baru.
- Hasil return `reason: 'no_active_wave'` → tidak ada registration yang dibuat untuk program tersebut.
- Admin harus memastikan gelombang aktif tersedia di `/admin/gelombang` **sebelum** approve mahasiswa.

---

## 6. Algoritma Penetapan

### Gambaran Besar

```
assign(MahasiswaProfile $profile)
    │
    ├─ Cek gelombang aktif untuk setiap program
    │       ↓ tidak ada gelombang → skip program tersebut
    │
    ├─ Apakah program_choice = 'PKPPM'?
    │   ├─ Ya  → Langsung Kondisi 2 (cari pasangan terdekat)
    │   └─ Tidak → Kondisi 1 (radius) → jika gagal → Kondisi 2
    │
    └─ saveAndReturn() → Registration::create() + kembalikan $results
```

---

### Kondisi 1 — Radius dari Domisili *(hanya untuk KPM atau PPL saja)*

**Kapan dipakai:** Program `'KPM'` atau `'PPL'` (bukan PKPPM).

**Cara kerja:**

```
1. Ambil semua school: is_active=true, program=$program
2. Hitung jarak Haversine dari (mahasiswa.lat, mahasiswa.lng) ke setiap school
3. Filter: jarak ≤ max_radius_km DAN availableSlots() > 0
4. Urutkan ascending berdasarkan jarak
5. Ambil yang pertama (terdekat)
```

**Hasil:**
- Ditemukan → ditetapkan, `method = 'domisili'`
- Tidak ditemukan → lanjut ke **Kondisi 2**

**Contoh:**

```
Mahasiswa: lat=-6.200, lng=106.816
Radius: 10 km

Pool KPM yang aktif & ada kuota:
  Desa Sukamaju  → jarak 3.2 km ✓ (dalam radius)
  Desa Harapan   → jarak 7.8 km ✓ (dalam radius)
  Desa Jauh      → jarak 15.1 km ✗ (luar radius)
  Desa Penuh     → jarak 2.5 km ✗ (kuota habis)

→ Penetapan: Desa Sukamaju (3.2 km) dengan method='domisili'
```

---

### Kondisi 2 — Proximity / Kedekatan Antar-Lokasi

**Kapan dipakai:**
- Selalu untuk **PKPPM** (penetapan pasangan desa+sekolah)
- Sebagai **fallback** untuk KPM/PPL jika tidak ada lokasi dalam radius

Kondisi 2 memiliki 4 sub-skenario tergantung ketersediaan lokasi:

#### Sub-skenario A: PKPPM — Kedua Program Belum Ditetapkan (normal)

Ini skenario utama PKPPM. Sistem mencari **pasangan** (satu KPM × satu PPL) yang jarak antar keduanya paling kecil.

```
1. Ambil semua KPM candidates: is_active=true, program='KPM', availableSlots()>0
2. Ambil semua PPL candidates: is_active=true, program='PPL', availableSlots()>0
3. Hitung jarak setiap kombinasi (KPM × PPL) → O(n×m) iterasi
4. Pilih pasangan dengan jarak antar-lokasi terkecil
5. Tetapkan KPM + PPL sekaligus
```

```
Pool KPM: Desa A (lat -6.10, lng 106.80)
          Desa B (lat -6.30, lng 107.00)

Pool PPL: Sekolah X (lat -6.12, lng 106.82)
          Sekolah Y (lat -6.28, lng 107.05)

Matriks jarak:
              Sekolah X    Sekolah Y
  Desa A   │   2.8 km  │   31.2 km │
  Desa B   │   25.1 km │   5.6 km  │

→ Minimum: Desa A ↔ Sekolah X (2.8 km)
→ Penetapan: KPM=Desa A, PPL=Sekolah X
```

#### Sub-skenario B: PKPPM — Satu Sudah Ditetapkan, Satu Belum (anchor)

Terjadi ketika salah satu program sudah punya registration pending/approved sebelumnya. Yang sudah ada dijadikan "anchor", lalu sistem mencari lokasi program lain yang paling dekat ke anchor.

```
Misal: PPL sudah ditetapkan di Sekolah X
→ Cari KPM yang paling dekat ke Sekolah X
→ Method = 'proximity'
```

#### Sub-skenario C: Hanya Satu Pool yang Tersedia

Saat PKPPM tapi salah satu pool kosong:

```
KPM candidates kosong, PPL ada
→ PPL ditetapkan ke yang paling dekat dari DOMISILI mahasiswa
→ KPM: tidak ada penetapan (school = null)

PPL candidates kosong, KPM ada
→ KPM ditetapkan ke yang paling dekat dari DOMISILI mahasiswa
→ PPL: tidak ada penetapan (school = null)
```

> Perhatikan: fallback terakhir ini menggunakan **jarak ke domisili**, bukan antar-lokasi.

#### Sub-skenario D: Semua Pool Kosong

```
KPM candidates kosong DAN PPL candidates kosong
→ Tidak ada yang ditetapkan
→ method = 'proximity', school = null untuk semua program
→ Tidak ada Registration yang dibuat
```

---

### Flowchart Lengkap

```
assign(MahasiswaProfile $profile)
         │
         ▼
Untuk setiap program yang diperlukan:
         │
         ├─ Sudah ada registration pending/approved? → SKIP
         │
         ├─ Ada gelombang aktif & terbuka?
         │       └─ TIDAK → reason='no_active_wave', SKIP
         │
         ▼
PKPPM? ─────────────────────────────────────────────────────────
  │ YA                                                          │ TIDAK
  │                                                             │
  ▼                                                             ▼
Kondisi 2 (langsung)                          Kondisi 1: cari dalam radius
  → sub-skenario A/B/C/D                        │
                                                ├─ Ditemukan → method='domisili' ✓
                                                │
                                                └─ Tidak → Kondisi 2 (fallback)
                                                     → sub-skenario B/C/D
         │
         ▼
saveAndReturn():
  → Hitung distance_km dari domisili ke school yang ditetapkan
  → Registration::create(status='pending')
  → Return $results[]
```

---

## 7. Edge Cases Lengkap

| Situasi | Perilaku Sistem |
|---------|----------------|
| Tidak ada gelombang aktif | `reason='no_active_wave'`, tidak ada registration dibuat |
| Gelombang sudah tutup (tanggal_tutup terlewat) | Sama seperti tidak ada gelombang |
| Semua lokasi dalam radius penuh (kuota=0) | Lanjut ke Kondisi 2 |
| Tidak ada lokasi sama sekali di pool | `school=null`, tidak ada registration |
| PKPPM, salah satu pool kosong | Program yang tersedia ditetapkan ke terdekat dari domisili; yang lain tidak ada penetapan |
| Mahasiswa sudah punya registration pending untuk program X | Program X di-skip, tidak ada registration baru |
| Mahasiswa punya registration approved untuk program X | Program X di-skip |
| Mahasiswa punya registration rejected untuk program X | **Tidak** di-skip — bisa ditetapkan ulang |
| `latitude`/`longitude` mahasiswa = 0,0 (belum diisi) | Jarak dihitung dari titik (0,0) → kemungkinan salah. Tidak ada validasi built-in. |
| `latitude`/`longitude` lokasi = 0,0 (belum diisi) | Sama — jarak tidak akurat. |
| Kuota = 0 dari awal | Lokasi tidak masuk pool, diabaikan sepenuhnya |
| `is_active = false` | Lokasi diabaikan sepenuhnya |

---

## 8. Return Value `assign()`

`assign()` mengembalikan array dengan struktur berikut:

```php
[
    'KPM' => [
        'school'    => School|null,   // Model School yang ditetapkan, null jika gagal
        'method'    => string|null,   // Lihat tabel di bawah
        'gelombang' => Gelombang|null,// Model Gelombang yang dipakai
        'reason'    => string|null,   // Hanya ada jika method=null
    ],
    'PPL' => [ /* struktur sama */ ],
]
```

### Nilai `method`

| Nilai | Artinya |
|-------|---------|
| `'domisili'` | Ditetapkan via Kondisi 1 (radius dari domisili) |
| `'proximity'` | Ditetapkan via Kondisi 2 (kedekatan antar-lokasi) |
| `'pending'` | (internal, sementara) Menunggu diproses |
| `'pending_proximity'` | (internal, sementara) Menunggu Kondisi 2 |
| `null` | Penetapan gagal; lihat `reason` |

### Nilai `reason` (hanya saat `method = null`)

| Nilai | Artinya |
|-------|---------|
| `'no_active_wave'` | Tidak ada gelombang aktif/terbuka untuk program ini |

### Contoh return value sukses (PKPPM):

```php
[
    'KPM' => [
        'school'    => School { name: 'Desa Sukamaju', ... },
        'method'    => 'proximity',
        'gelombang' => Gelombang { nomor: 1, tahun_akademik: '2024/2025' },
        'reason'    => null,
    ],
    'PPL' => [
        'school'    => School { name: 'SMAN 3 Jakarta', ... },
        'method'    => 'proximity',
        'gelombang' => Gelombang { nomor: 2, tahun_akademik: '2024/2025' },
        'reason'    => null,
    ],
]
```

### Contoh return value gagal (tidak ada gelombang):

```php
[
    'KPM' => [
        'school'    => null,
        'method'    => null,
        'gelombang' => null,
        'reason'    => 'no_active_wave',
    ],
    'PPL' => null,  // tidak relevan untuk program KPM saja
]
```

---

## 9. Kalkulasi Jarak — Formula Haversine

Semua kalkulasi jarak menggunakan `Geo::distanceKm()`:

```php
// app/Support/Geo.php
public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earthRadius = 6371.0;  // km

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
```

**Penjelasan formula:**
- Haversine menghitung jarak "garis lurus di permukaan bumi" (great-circle distance)
- Akurat untuk jarak pendek dan menengah (< 1000 km)
- Mengasumsikan bumi sebagai bola sempurna radius 6371 km
- Hasilnya dalam **kilometer** (floating point)
- Satuan input: derajat desimal (decimal degrees), bukan DMS (derajat°menit'detik")

**Contoh penggunaan:**

```php
$jarak = Geo::distanceKm(
    -6.200, 106.816,  // lat/lng mahasiswa (Jakarta Selatan)
    -6.175, 106.827,  // lat/lng sekolah
);
// → ~2.9 km
```

---

## 10. Konfigurasi & Caching

### Parameter yang Dapat Dikonfigurasi

| Parameter | Key di `settings` | Default | Cara Ubah |
|-----------|-------------------|---------|-----------|
| Radius maksimum Kondisi 1 | `max_radius_km` | `10` | `/admin/settings` |
| Kuota KPM per lokasi | kolom `kuota_kpm` di `schools` | `0` | `/admin/schools/{id}/edit` |
| Kuota PPL per lokasi | kolom `kuota_ppl` di `schools` | `0` | `/admin/schools/{id}/edit` |

### Mekanisme Caching Setting

`Setting::get()` menggunakan `Cache::rememberForever`:

```php
// Setting.php
public static function get(string $key, $default = null)
{
    return Cache::rememberForever("setting.$key", function () use ($key, $default) {
        return optional(static::where('key', $key)->first())->value ?? $default;
    });
}

public static function put(string $key, $value): void
{
    static::updateOrCreate(['key' => $key], ['value' => $value]);
    Cache::forget("setting.$key");  // ← cache dihapus saat update
}
```

> **Penting:** Selalu gunakan `Setting::put()` (bukan update langsung ke database) saat mengubah setting, agar cache ikut di-invalidate. Jika edit database manual, jalankan `php artisan cache:clear` agar perubahan radius terbaca.

---

## 11. Cara Dipanggil — Entry Point

Auto-assign dipanggil di `MahasiswaManagementController::approve()`:

```php
// MahasiswaManagementController.php
class MahasiswaManagementController extends Controller
{
    public function __construct(private AutoAssignService $autoAssign) {}
    //                          ↑ dependency injection via konstruktor

    public function approve(Request $request, MahasiswaProfile $mahasiswa)
    {
        // 1. Tandai profil sebagai approved
        $mahasiswa->update([
            'status'      => 'approved',
            'admin_note'  => $request->validate(['admin_note' => 'nullable|string'])['admin_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        // 2. Jalankan auto-assign
        $results = $this->autoAssign->assign($mahasiswa);

        // 3. Buat flash message dari hasil
        $assigned = collect($results)
            ->filter(fn ($v) => $v !== null && ($v['school'] ?? null))
            ->map(fn ($v, $k) => "$k: {$v['school']->name} (via {$v['method']})")
            ->implode(', ');

        $msg = $assigned
            ? "Mahasiswa disetujui. Penempatan otomatis: $assigned."
            : 'Mahasiswa disetujui. Tidak ada lokasi dengan kuota tersedia.';

        return back()->with('status', $msg);
    }
}
```

**Urutan kejadian:**
1. Admin klik "Setujui" di `/admin/mahasiswa/{id}`
2. Status profil → `'approved'`
3. `AutoAssignService::assign()` dipanggil
4. Gelombang dicek untuk setiap program
5. Algoritma penetapan berjalan
6. `Registration::create()` dipanggil untuk setiap program yang berhasil
7. Flash message muncul dengan ringkasan hasil

---

## 12. Panduan Re-Implementasi di Project Lain

Bagian ini menjelaskan langkah-langkah untuk menerapkan fitur ini di project Laravel lain dari nol.

### Langkah 1: Buat Tabel yang Diperlukan

```php
// Migration: create_locations_table (ganti 'schools' → 'locations' sesuai domain Anda)
Schema::create('locations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('address');
    $table->decimal('latitude', 10, 7);   // ← wajib ada
    $table->decimal('longitude', 10, 7);  // ← wajib ada
    $table->string('program');            // nilai domain Anda (misal: 'TYPE_A', 'TYPE_B')
    $table->unsignedInteger('quota_a')->default(0);
    $table->unsignedInteger('quota_b')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration: create_assignments_table (ganti 'registrations' sesuai domain Anda)
Schema::create('assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('profile_id')->constrained('user_profiles')->cascadeOnDelete();
    $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
    $table->string('program');
    $table->decimal('distance_km', 8, 3);
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();

    $table->unique(['profile_id', 'program']); // ← wajib: satu user, satu program
});

// Migration: create_settings_table
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->timestamps();
});
```

### Langkah 2: Salin File Helper

Salin file berikut dan sesuaikan namespace:

```
app/Support/Geo.php           → salin apa adanya
app/Services/AutoAssignService.php  → sesuaikan nama model
app/Models/Setting.php        → salin apa adanya
```

### Langkah 3: Tambahkan Method ke Model Lokasi

```php
// app/Models/Location.php
public function availableSlots(string $program): int
{
    $quota = $program === 'TYPE_A' ? $this->quota_a : $this->quota_b;
    $taken = $this->assignments()
        ->where('program', $program)
        ->whereIn('status', ['pending', 'approved'])
        ->count();
    return max(0, $quota - $taken);
}
```

### Langkah 4: Tambahkan Method ke Model User/Profile

```php
// app/Models/UserProfile.php
public function programsToAssign(): array
{
    return match ($this->program_choice) {
        'TYPE_A'   => ['TYPE_A'],
        'TYPE_B'   => ['TYPE_B'],
        'BOTH'     => ['TYPE_A', 'TYPE_B'],
        default    => [],
    };
}
```

### Langkah 5: Sesuaikan AutoAssignService

Ganti nama class model di `AutoAssignService.php`:

```php
// Ganti:
use App\Models\School;        → use App\Models\Location;
use App\Models\Registration;  → use App\Models\Assignment;
use App\Models\Gelombang;     → hapus (jika tidak pakai gelombang)

// Di findNearestWithinRadius():
School::query()   → Location::query()
->where('program', $program)  → tetap sama
->get()           → tetap sama
...availableSlots($program)   → tetap sama

// Di getAllWithSlots():
School::query()   → Location::query()
```

Jika project Anda **tidak menggunakan sistem gelombang**, hapus seluruh blok cek gelombang:

```php
// Hapus blok ini dari assign():
$gelombang = Gelombang::activeFor($program);
if (! $gelombang || ! $gelombang->isOpen()) { ... }

// Ubah saveAndReturn() — hapus gelombang_id dari create():
Assignment::create([
    'profile_id'  => $profile->id,
    'location_id' => $location->id,
    // 'gelombang_id' → hapus
    'program'     => $program,
    'distance_km' => round($distance, 3),
    'status'      => 'pending',
]);
```

### Langkah 6: Daftarkan di Container (opsional)

Laravel otomatis resolve via type-hint. Tapi jika perlu konfigurasi eksplisit:

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(AutoAssignService::class);
}
```

### Langkah 7: Panggil di Controller

```php
class UserApprovalController extends Controller
{
    public function __construct(private AutoAssignService $autoAssign) {}

    public function approve(UserProfile $profile)
    {
        $profile->update(['status' => 'approved']);
        $results = $this->autoAssign->assign($profile);
        // handle $results ...
        return back()->with('status', 'Berhasil disetujui.');
    }
}
```

### Langkah 8: Isi Data Awal Settings

```php
// Seeder atau tinker
Setting::put('max_radius_km', '10');
```

### Checklist Re-Implementasi

- [ ] Tabel lokasi dengan kolom `latitude`, `longitude`, `program`, `quota_*`, `is_active`
- [ ] Tabel assignments dengan unique constraint `(profile_id, program)`
- [ ] Tabel settings dengan key `max_radius_km`
- [ ] `Geo::distanceKm()` tersedia
- [ ] `availableSlots()` di model lokasi
- [ ] `programsToAssign()` di model profile
- [ ] `Setting::get()` + `Setting::put()` dengan caching
- [ ] Gelombang diimplementasikan (atau blok cek gelombang dihapus)
- [ ] `AutoAssignService::assign()` dipanggil saat approval
- [ ] Data lokasi sudah terisi dengan `latitude`/`longitude` valid
- [ ] Data profile mahasiswa sudah terisi dengan `latitude`/`longitude` valid
- [ ] Kuota lokasi > 0 (jika 0, tidak ada yang bisa ditetapkan)

---

## 13. Troubleshooting

### "Mahasiswa disetujui tapi tidak ada penetapan"

**Kemungkinan penyebab (cek berurutan):**

1. **Tidak ada gelombang aktif** → Buat/aktifkan gelombang di `/admin/gelombang`
2. **Gelombang sudah tutup** → Cek `tanggal_tutup` di tabel gelombang
3. **Semua lokasi tidak aktif** → Cek `is_active = true` di tabel schools
4. **Kuota semua lokasi = 0** → Set `kuota_kpm` atau `kuota_ppl` > 0
5. **Mahasiswa sudah punya registration pending/approved** → Sistem skip; cek tabel registrations
6. **Tidak ada lokasi dengan program yang sesuai** → Pastikan ada school dengan `program='KPM'` (untuk KPM) atau `program='PPL'` (untuk PPL)

### "Mahasiswa ditetapkan di lokasi yang jauh"

- Radius diperbesar tapi tidak ada lokasi dalam radius → Kondisi 2 dipilih (terdekat yang ada)
- `latitude`/`longitude` mahasiswa salah atau kosong (0,0) → Update koordinat di profil mahasiswa
- Kondisi 2 memilih berdasarkan jarak antar-lokasi (PKPPM), bukan jarak ke mahasiswa → Ini by design

### "Perubahan radius tidak berpengaruh"

- Setting di-cache `rememberForever` → Jalankan `php artisan cache:clear` atau gunakan `Setting::put('max_radius_km', '15')` yang otomatis hapus cache

### "Error: Duplicate entry for key 'registrations_mahasiswa_profile_id_program_unique'"

- Mahasiswa sudah punya registration untuk program tersebut tapi dengan status `'rejected'` atau `'cancelled'` — lalu admin approve ulang
- Guard `alreadyRegistered()` hanya mengecek `pending` dan `approved`, bukan `rejected`/`cancelled`
- **Solusi:** Hapus registration lama yang rejected/cancelled sebelum approve ulang, atau tambahkan status `rejected`/`cancelled` ke guard

### "Kompleksitas PKPPM lambat saat banyak lokasi"

- Algoritma pasangan O(n×m) — berjalan sekali saat approval, bukan request biasa
- Untuk 100 KPM × 100 PPL = 10.000 kalkulasi → masih cepat (< 100ms)
- Jika ribuan lokasi: pertimbangkan optimasi dengan spatial index atau pre-filter radius dulu
