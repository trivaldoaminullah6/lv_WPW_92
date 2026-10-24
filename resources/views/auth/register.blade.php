<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Baru - Acara 32</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary">Registrasi Praktikum</h3>
                    <p class="text-muted small">Pendaftaran otomatis mengirim <strong>Welcome Mail</strong> dan <strong>Email Verifikasi</strong></p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('mail.register.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="budi@example.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 6 karakter">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary fw-semibold py-2">Daftar & Kirim Email</button>
                        <a href="{{ route('mail.index') }}" class="btn btn-outline-secondary py-2">Kembali ke Dashboard Email</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
