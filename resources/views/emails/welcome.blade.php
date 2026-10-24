<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #0d6efd; margin: 0; font-size: 22px; }
        .content { line-height: 1.6; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 15px; }
        .badge { background: #e7f1ff; color: #0d6efd; padding: 4px 8px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Praktikum Workshop Web Framework (Acara 32)</h1>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $user->name }}</strong>!</p>
            <p>Terima kasih telah bergabung. Akun Anda berhasil didaftarkan dengan alamat email: <span class="badge">{{ $user->email }}</span>.</p>
            <p>Ini adalah email selamat datang yang dikirim secara otomatis melalui fitur <strong>Laravel Mailable</strong>.</p>
            <p>Silakan lanjutkan proses praktikum untuk menguji fitur verifikasi email, queue antrean email, dan email notifikasi markdown.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Workshop Sistem Informasi Web Framework - D4 Teknik Informatika Polije</p>
        </div>
    </div>
</body>
</html>
