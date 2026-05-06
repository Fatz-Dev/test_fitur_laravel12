<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Verifikasi Email — SIPEP</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#1a1a2e;padding:32px 16px}
  .card{max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,35,111,.10)}
  .header{background:#00236f;padding:32px 40px;text-align:center}
  .header h1{color:#fff;font-size:28px;font-weight:800;letter-spacing:-0.5px}
  .header p{color:#7eb4ff;font-size:13px;margin-top:4px}
  .body{padding:36px 40px}
  .greeting{font-size:16px;color:#333;margin-bottom:16px}
  .message{font-size:15px;color:#555;line-height:1.7;margin-bottom:28px}
  .btn{display:inline-block;background:#006a61;color:#fff!important;text-decoration:none;padding:14px 36px;border-radius:10px;font-size:15px;font-weight:700;letter-spacing:.2px}
  .btn:hover{background:#005249}
  .divider{border:none;border-top:1px solid #e8ecf8;margin:28px 0}
  .fallback{font-size:12px;color:#888;line-height:1.6;word-break:break-all}
  .fallback a{color:#00236f}
  .footer{background:#f8f9ff;padding:20px 40px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #e8ecf8}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>SIPEP</h1>
    <p>Sistem Informasi Praktik &amp; Edukasi Profesional</p>
  </div>
  <div class="body">
    <p class="greeting">Halo, <strong>{{ $user->name }}</strong>!</p>
    <p class="message">
      Terima kasih telah mendaftar di SIPEP. Klik tombol di bawah ini untuk memverifikasi alamat email Anda.
      Link ini akan kedaluwarsa dalam <strong>24 jam</strong>.
    </p>
    <div style="text-align:center;margin:28px 0">
      <a href="{{ $verifyUrl }}" class="btn">Verifikasi Email Saya</a>
    </div>
    <hr class="divider"/>
    <p class="fallback">
      Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:<br/>
      <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
    </p>
    <hr class="divider"/>
    <p style="font-size:13px;color:#999">
      Jika Anda tidak merasa mendaftar akun SIPEP, abaikan email ini.
    </p>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} SIPEP Universitas. Semua hak cipta dilindungi.
  </div>
</div>
</body>
</html>
