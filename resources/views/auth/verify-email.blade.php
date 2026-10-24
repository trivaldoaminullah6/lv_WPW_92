<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda - Acara 32</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card card-custom p-4 bg-white text-center">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#0d6efd" class="bi bi-envelope-check" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.549-.549a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .76-.06l1.5-2.5a.5.5 0 0 0-.147-.721"/>
                    </svg>
                </div>

                <h3 class="fw-bold mb-2">Verifikasi Alamat Email Anda</h3>
                <p class="text-muted mb-3">
                    @php
                        $rawEmail = $user ? $user->email : '';
                        $eparts = explode('@', $rawEmail);
                        $ename = $eparts[0] ?? '';
                        $edomain = $eparts[1] ?? '';
                        $maskedUserEmail = strlen($ename) > 3
                            ? substr($ename, 0, 2) . '*****' . substr($ename, -1) . '@' . $edomain
                            : substr($ename, 0, 1) . '***@' . $edomain;
                    @endphp
                    Terima kasih telah mendaftar, <strong>{{ $maskedUserEmail }}</strong>!
                </p>

                @if(!empty($isLogDriver) && !empty($verificationUrl))
                    <div class="alert alert-info text-start small mb-4">
                        <strong class="d-block mb-1">Informasi Mode Praktikum (Driver: LOG):</strong>
                        Pada konfigurasi bawaan, sistem menggunakan driver log sehingga email tidak dikirimkan ke server internet/inbox Gmail luar, melainkan disimpan di sistem.
                        <div class="mt-3 text-center">
                            <a href="{{ $verificationUrl }}" class="btn btn-success fw-bold px-4 py-2 w-100">
                                Verifikasi Akun Sekarang (Simulasi)
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-4">
                        Tautan verifikasi telah dikirimkan ke alamat email Anda. Silakan periksa kotak masuk atau spam email Anda.
                    </p>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-2">
                    <form action="{{ route('verification.send') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary fw-semibold px-3">
                            Kirim Ulang Email (Resend)
                        </button>
                    </form>
                    <a href="{{ route('mail.index') }}" class="btn btn-outline-secondary px-3">
                        Lihat Dashboard Mail
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
