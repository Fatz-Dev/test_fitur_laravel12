# Fitur Pemilihan Lokasi Otomatis (Auto-Assign)

## Gambaran Umum

SIPEP menetapkan lokasi KPM dan PPL untuk mahasiswa secara otomatis setelah profil disetujui admin. Tidak ada pemilihan manual oleh mahasiswa — sistem bekerja penuh berdasarkan data domisili, program yang dipilih, dan ketersediaan kuota lokasi.

---

## Tipe Lokasi

Setiap lokasi di `/admin/schools` hanya memiliki **satu program**:

| Program | Tipe Lokasi | Keterangan |
|---------|-------------|------------|
| **KPM** | Desa | Desa, kelurahan, atau kecamatan |
| **PPL** | Sekolah | SD, SMP, SMA, SMK, MI, MTs, MA |

> **Tidak ada tipe "Keduanya/BOTH".** Mahasiswa PKPPM mendapatkan dua lokasi terpisah — satu dari pool KPM dan satu dari pool PPL. Sistem yang menentukan pasangan terbaik.

---

## Program Mahasiswa

| Pilihan | Program yang Dijalankan | Jumlah Lokasi |
|---------|------------------------|---------------|
| **KPM saja** | KPM | 1 (desa) |
| **PPL saja** | PPL | 1 (sekolah) |
| **PKPPM** | KPM + PPL | 2 (1 desa + 1 sekolah) |

---

## Dua Kondisi Penetapan Lokasi

### Kondisi 1 — Berbasis Radius *(untuk KPM saja atau PPL saja)*

Sistem mencari lokasi terdekat dalam radius yang dapat dikonfigurasi (default **10 km**) dari koordinat domisili mahasiswa.

**Alur:**
1. Ambil semua lokasi aktif dengan program yang sesuai (KPM atau PPL).
2. Hitung jarak dari domisili mahasiswa ke setiap lokasi menggunakan formula Haversine.
3. Filter lokasi yang berada dalam radius dan masih memiliki kuota tersedia.
4. Pilih lokasi **terdekat** dari hasil filter.
5. Jika ada lokasi yang memenuhi syarat → **langsung ditetapkan** (selesai).
6. Jika tidak ada → lanjut ke **Kondisi 2**.

**Contoh:**
```
Mahasiswa domisili: Jl. Merdeka No. 1, Kota A
Radius: 10 km
Lokasi KPM tersedia dalam 10 km: Desa Sukamaju (3.2 km), Desa Harapan (7.8 km)
→ Penetapan: Desa Sukamaju (terdekat)
```

---

### Kondisi 2 — Berbasis Kedekatan Antar-Lokasi *(selalu untuk PKPPM; fallback untuk KPM/PPL)*

Sistem mencari pasangan desa KPM + sekolah PPL yang **jarak antar keduanya paling kecil**, bukan jarak ke domisili mahasiswa. Tujuannya meminimalkan jarak tempuh mahasiswa antara dua lokasi penempatan.

**Alur untuk PKPPM (keduanya belum ditetapkan):**
1. Ambil semua lokasi KPM aktif dengan kuota tersedia.
2. Ambil semua lokasi PPL aktif dengan kuota tersedia.
3. Hitung jarak antara setiap kombinasi pasangan (KPM × PPL).
4. Pilih pasangan dengan jarak antar-lokasi **terkecil**.
5. Tetapkan keduanya sekaligus.

**Alur fallback (satu sudah ditetapkan, satu belum):**
1. Gunakan lokasi yang sudah ditetapkan sebagai "jangkar" (anchor).
2. Cari lokasi lain yang paling dekat ke jangkar tersebut.

**Alur fallback (tidak ada lokasi sama sekali):**
- Tidak ada penetapan. Mahasiswa perlu menghubungi admin.

**Contoh PKPPM:**
```
Pool KPM: Desa A (lat -6.1, lng 106.8), Desa B (lat -6.3, lng 107.0)
Pool PPL: Sekolah X (lat -6.12, lng 106.82), Sekolah Y (lat -6.28, lng 107.05)

Kombinasi:
  Desa A ↔ Sekolah X: 2.8 km  ← pasangan terpilih
  Desa A ↔ Sekolah Y: 31.2 km
  Desa B ↔ Sekolah X: 25.1 km
  Desa B ↔ Sekolah Y: 5.6 km

→ Penetapan: Desa A (KPM) + Sekolah X (PPL)
```

---

## Kapan Penetapan Dijalankan?

Penetapan otomatis dijalankan **saat admin menyetujui profil mahasiswa** melalui halaman `/admin/mahasiswa/{id}` → tombol "Setujui & Tetapkan Penempatan".

Setelah penetapan:
- Data tersimpan di tabel `registrations` dengan status `pending`.
- Admin dapat mengonfirmasi (approve) atau menolak (reject) masing-masing penempatan di `/admin/registrations`.
- Mahasiswa dapat melihat status penempatan di dashboard mereka.

---

## Konfigurasi

| Parameter | Lokasi | Default | Keterangan |
|-----------|--------|---------|------------|
| Radius maksimum | `/admin/settings` | 10 km | Digunakan pada Kondisi 1 |
| Kuota KPM per lokasi | `/admin/schools` (per lokasi) | — | Jumlah mahasiswa KPM yang dapat ditempatkan |
| Kuota PPL per lokasi | `/admin/schools` (per lokasi) | — | Jumlah mahasiswa PPL yang dapat ditempatkan |

---

## File yang Terkait

| File | Fungsi |
|------|--------|
| `app/Services/AutoAssignService.php` | Logika utama penetapan otomatis |
| `app/Models/School.php` | Model lokasi; `availableSlots()`, `acceptsProgram()`, `locationType()` |
| `app/Support/Geo.php` | Kalkulasi jarak Haversine |
| `app/Http/Controllers/Admin/MahasiswaController.php` | Memanggil `AutoAssignService::assign()` saat approve |
| `app/Models/Setting.php` | Membaca `max_radius_km` dari database |

---

## Ringkasan Alur Lengkap

```
Profil mahasiswa disetujui admin
         ↓
    Pilihan program?
    ┌───────────────────────────────┐
    │ KPM saja / PPL saja           │ → Kondisi 1 (radius dari domisili)
    │                               │   ├─ Ada lokasi dalam radius → Tetapkan
    │                               │   └─ Tidak ada → Kondisi 2 (terdekat ke anchor/domisili)
    ├───────────────────────────────┤
    │ PKPPM                         │ → Langsung Kondisi 2
    │ (KPM + PPL sekaligus)         │   Cari pasangan desa+sekolah terdekat satu sama lain
    └───────────────────────────────┘
         ↓
  Simpan ke tabel registrations (status: pending)
         ↓
  Admin konfirmasi di /admin/registrations
```
