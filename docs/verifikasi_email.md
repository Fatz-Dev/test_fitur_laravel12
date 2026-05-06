# Fitur Verifikasi Email & Ubah Password — SIPEP

## Daftar Isi
1. [Ringkasan](#ringkasan)
2. [Verifikasi Email saat Registrasi](#verifikasi-email-saat-registrasi)
3. [Ubah Password](#ubah-password)
4. [Konfigurasi Mail (SMTP)](#konfigurasi-mail-smtp)
5. [Alur Lengkap](#alur-lengkap)
6. [Daftar File Terkait](#daftar-file-terkait)
7. [Troubleshooting](#troubleshooting)

---

## Ringkasan

SIPEP mengimplementasikan dua fitur keamanan akun:

| Fitur | Deskripsi |
|-------|-----------|
| **Verifikasi Email** | Setelah registrasi, mahasiswa wajib memverifikasi email sebelum bisa login |
| **Ubah Password** | Pengguna yang sudah login dapat mengganti password dari sidebar |

---

## Verifikasi Email saat Registrasi

### Mekanisme

Fitur ini menggunakan **Signed URL** bawaan Laravel (`URL::temporarySignedRoute`) — tidak membutuhkan tabel database tambahan. Token keamanan disematkan langsung dalam URL dengan TTL **24 jam**.

### Alur

```
[Mahasiswa Daftar]
       │
       ▼
[User dibuat, email_verified_at = NULL]
       │
       ▼
[Email verifikasi dikirim via SMTP Mailtrap]
       │
       ▼
[Redirect ke /verify-email — halaman "Cek Email Anda"]
       │
       ├─ Mahasiswa klik link di email ──▶ [GET /email/verify/{id}?hash=...&signature=...]
       │                                           │
       │                                           ▼
       │                              [Signature valid? Hash cocok?]
       │                                     │              │
       │                                    YA             TIDAK
       │                                     │              │
       │                              [email_verified_at   [Redirect ke /verify-email
       │                               di-set = now()]      dengan pesan error]
       │                                     │
       │                                     ▼
       │                              [JWT cookie diset]
       │                                     │
       │                                     ▼
       │                              [Redirect ke Dashboard]
       │
       └─ Tidak ada email? ──▶ Form "Kirim Ulang" di halaman /verify-email
```

### Perlindungan Login

Jika mahasiswa mencoba login **sebelum** memverifikasi email:

```
Login → Cek email_verified_at → NULL → Blokir + tampilkan pesan error
                                              │
                                              ▼
                              [Tombol "Kirim ulang link verifikasi →"]
```

Pesan error yang muncul:
> *"Email belum diverifikasi. Cek inbox Anda atau minta link verifikasi baru."*

### Keamanan Signed URL

Laravel menandatangani URL dengan `APP_KEY`, sehingga:
- URL tidak bisa dipalsukan
- URL kedaluwarsa otomatis setelah 24 jam
- Hash email (`sha1($user->email)`) memastikan link hanya berlaku untuk email yang benar

Contoh URL yang dikirim di email:
```
https://sipep.ac.id/email/verify/42?expires=1778131134&hash=567159d6...&signature=abc123...
```

### Kirim Ulang Verifikasi

- Tersedia di halaman `/verify-email` dan di halaman login (muncul otomatis saat login ditolak karena belum verifikasi)
- Endpoint: `POST /email/resend`
- Membutuhkan input email yang valid dan terdaftar di sistem

---

## Ubah Password

### Akses

Link **"Ubah Password"** tersedia di **sidebar bawah** (di atas tombol Keluar) untuk semua pengguna yang sudah login (admin maupun mahasiswa).

### Endpoint

| Method | URL | Nama Route |
|--------|-----|------------|
| GET | `/account/password` | `account.password.edit` |
| PUT | `/account/password` | `account.password.update` |

### Validasi

| Field | Aturan |
|-------|--------|
| `current_password` | Wajib, harus cocok dengan password aktif di database |
| `password` | Wajib, minimal 8 karakter, harus dikonfirmasi |
| `password_confirmation` | Harus sama dengan `password` |

### Fitur UI

- **Tombol show/hide password** — pada setiap field password
- **Password strength meter** — 4 segmen (Lemah / Cukup / Kuat / Sangat Kuat) yang berwarna dinamis saat user mengetik

#### Kriteria Kekuatan Password

| Skor | Label | Kriteria |
|------|-------|----------|
| 1 | Lemah | Panjang ≥ 8 karakter |
| 2 | Cukup | Panjang ≥ 12 karakter |
| 3 | Kuat | Ada huruf besar + kecil |
| 4 | Sangat Kuat | Ada angka + karakter spesial |

---

## Konfigurasi Mail (SMTP)

### Environment Variables

| Variable | Nilai | Sifat |
|----------|-------|-------|
| `MAIL_MAILER` | `smtp` | Non-sensitif (env var) |
| `MAIL_HOST` | `sandbox.smtp.mailtrap.io` | Non-sensitif (env var) |
| `MAIL_PORT` | `2525` | Non-sensitif (env var) |
| `MAIL_ENCRYPTION` | `tls` | Non-sensitif (env var) |
| `MAIL_FROM_ADDRESS` | `noreply@sipep.ac.id` | Non-sensitif (env var) |
| `MAIL_FROM_NAME` | `SIPEP` | Non-sensitif (env var) |
| `MAIL_USERNAME` | *(dari Mailtrap SMTP Settings)* | **Secret** |
| `MAIL_PASSWORD` | *(dari Mailtrap SMTP Settings)* | **Secret** |

> **Penting:** `MAIL_USERNAME` dan `MAIL_PASSWORD` disimpan sebagai **Replit Secrets** (bukan env var biasa) agar tidak terekspos di kode.

### Cara Mendapatkan Credentials Mailtrap

1. Login ke [mailtrap.io](https://mailtrap.io)
2. Klik **Email Testing** → **Inboxes**
3. Klik nama inbox Anda
4. Klik tab **SMTP Settings**
5. Pilih integrasi **Laravel 9+** dari dropdown
6. Salin nilai `MAIL_USERNAME` dan `MAIL_PASSWORD` yang muncul

Contoh tampilan di Mailtrap:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=a1b2c3d4e5f6      ← salin ini
MAIL_PASSWORD=xyz789uvw012345   ← salin ini
MAIL_ENCRYPTION=tls
```

### Test Koneksi SMTP

```bash
php artisan tinker --execute="
\Illuminate\Support\Facades\Mail::raw('Test SMTP', function(\$m) {
    \$m->to('test@example.com')->subject('Test SIPEP');
});
echo 'OK';
"
```

---

## Alur Lengkap

### Registrasi Baru (dengan Verifikasi Email)

```
1. Mahasiswa buka /register → isi form (nama, email kampus, password)
2. POST /register
3. User dibuat di tabel users (email_verified_at = NULL)
4. Email verifikasi dikirim ke inbox mahasiswa
5. Redirect ke /verify-email → halaman "Cek Email Anda"
6. Mahasiswa buka email → klik "Verifikasi Email Saya"
7. GET /email/verify/{id}?hash=...&expires=...&signature=...
8. Middleware 'signed' memvalidasi URL
9. Controller set email_verified_at = now()
10. JWT cookie diset → redirect ke /mahasiswa/dashboard
11. Mahasiswa melengkapi profil (NIM, alamat, berkas, dll.)
```

### Login (dengan Cek Verifikasi)

```
1. Mahasiswa buka /login → isi email + password
2. POST /login
3. Cek: email + password cocok? → jika tidak → error "Email atau password salah"
4. Cek: email_verified_at tidak NULL? → jika NULL → error + link "Kirim ulang"
5. Jika lolos semua → JWT cookie diset → redirect ke dashboard
```

### Ubah Password

```
1. Pengguna klik "Ubah Password" di sidebar
2. GET /account/password → tampil form
3. Isi: password saat ini, password baru, konfirmasi
4. PUT /account/password
5. Validasi current_password cocok dengan hash di database
6. Update kolom password (otomatis di-hash oleh Laravel)
7. Redirect kembali ke form dengan flash "Password berhasil diubah."
```

---

## Daftar File Terkait

### Controllers

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/AuthController.php` | Register (kirim email verifikasi), Login (cek verified), Logout |
| `app/Http/Controllers/EmailVerificationController.php` | Tampil halaman notice, verifikasi link, kirim ulang email |
| `app/Http/Controllers/PasswordController.php` | Form & proses ubah password |

### Mail

| File | Fungsi |
|------|--------|
| `app/Mail/VerifyEmailMail.php` | Kelas Mailable untuk email verifikasi |
| `resources/views/emails/verify-email.blade.php` | Template HTML email verifikasi |

### Views

| File | Fungsi |
|------|--------|
| `resources/views/auth/verify-email.blade.php` | Halaman "Cek Email Anda" + form kirim ulang |
| `resources/views/auth/login.blade.php` | Login dengan tampilan error + link kirim ulang |
| `resources/views/account/change-password.blade.php` | Form ubah password |

### Routes

| File | Bagian yang Ditambahkan |
|------|------------------------|
| `routes/web.php` | `/verify-email`, `/email/verify/{id}`, `/email/resend`, `/account/password` |

### Layout

| File | Perubahan |
|------|-----------|
| `resources/views/layouts/app.blade.php` | Tambah link "Ubah Password" di sidebar bawah |

---

## Troubleshooting

### Email tidak masuk ke Mailtrap

| Kemungkinan Penyebab | Solusi |
|---------------------|--------|
| `MAIL_USERNAME` / `MAIL_PASSWORD` salah | Salin ulang dari halaman SMTP Settings Mailtrap |
| Credentials adalah email login Mailtrap | Gunakan credentials SMTP (bukan email login) |
| Port 2525 diblokir | Coba port `587` atau `465` |
| `APP_KEY` belum di-set | Jalankan `php artisan key:generate` |

### Link verifikasi "tidak valid atau kedaluwarsa"

| Kemungkinan Penyebab | Solusi |
|---------------------|--------|
| Link sudah >24 jam | Minta link baru lewat form di `/verify-email` |
| `APP_KEY` berubah setelah email dikirim | Link lama tidak valid, minta link baru |
| `APP_URL` tidak sesuai dengan domain aktual | Set `APP_URL` di env sesuai domain produksi |

### Error 403 saat klik link verifikasi

Hash email tidak cocok — kemungkinan email user berubah setelah link dikirim. Minta kirim ulang dari halaman `/verify-email`.

### "Password saat ini tidak sesuai"

User memasukkan password lama yang salah. Tidak ada fitur reset password (forgot password) saat ini — hubungi admin untuk reset manual.
