<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acara 32 - Laravel Email Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .log-box { background-color: #1e1e1e; color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 0.8rem; padding: 15px; border-radius: 8px; max-height: 320px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; }
        .badge-verified { background-color: #198754; color: white; }
        .badge-unverified { background-color: #ffc107; color: #212529; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Praktikum WSIWF</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link text-white-50" href="{{ route('image.index') }}">Acara 31: Image</a>
            <a class="nav-link active fw-semibold" href="{{ route('mail.index') }}">Acara 32: Email</a>
        </div>
    </div>
</nav>

<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="fw-bold mb-0">Acara 32: Laravel Email & Notification</h2>
                    <p class="text-muted">Mailable &middot; Email Verification &middot; Queue &middot; Markdown Template &middot; Notification</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary py-2 px-3 me-1">MAIL_MAILER: <strong>{{ $mailConfig['mailer'] }}</strong></span>
                    <span class="badge bg-info py-2 px-3 text-dark">QUEUE: <strong>{{ $mailConfig['queue'] }}</strong></span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Sukses!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Panel Tombol Pengujian -->
            <div class="card card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Pengujian Fitur Pengiriman Email</h5>
                    <a href="{{ route('mail.register') }}" class="btn btn-primary btn-sm px-3 fw-semibold">+ Registrasi User Baru (Uji Verifikasi)</a>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-light d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-primary">1. Mailable Sambutan</h6>
                                <p class="small text-muted mb-3">Kirim `WelcomeMail` secara sinkron (langsung) ke user terpilih.</p>
                            </div>
                            <form action="{{ route('mail.send.welcome') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100 btn-sm fw-semibold">Kirim WelcomeMail</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-light d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-success">2. Antrean Email (Queue)</h6>
                                <p class="small text-muted mb-2">Masukkan email ke antrean database (`Mail::queue`). Job pending: <strong>{{ $jobCount }}</strong>.</p>
                            </div>
                            <div>
                                <form action="{{ route('mail.send.queue') }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100 btn-sm fw-semibold">+ Masukkan ke Queue</button>
                                </form>
                                @if($jobCount > 0)
                                    <form action="{{ route('mail.process.queue') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100 btn-sm fw-semibold">Proses Antrean Sekarang ({{ $jobCount }})</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-light d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-warning text-dark">3. Markdown Mail</h6>
                                <p class="small text-muted mb-3">Kirim Mailable dengan template komponen Markdown (`NotifyUser`).</p>
                            </div>
                            <form action="{{ route('mail.send.markdown') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning text-dark w-100 btn-sm fw-semibold">Kirim Markdown Mail</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-light d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-danger">4. Notification</h6>
                                <p class="small text-muted mb-3">Kirim Laravel Notification `InvoicePaid` menggunakan channel mail.</p>
                            </div>
                            <form action="{{ route('mail.send.notification') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 btn-sm fw-semibold">Kirim Notification</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar User Terdaftar -->
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3">Daftar User & Status Verifikasi Email</h5>
                @if(count($users) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Status Verifikasi</th>
                                    <th>Tanggal Verifikasi</th>
                                    <th>Aksi Pengujian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    <tr>
                                        <td>{{ $u->id }}</td>
                                        <td class="fw-semibold">{{ $u->name }}</td>
                                        <td>
                                            @php
                                                $parts = explode('@', $u->email);
                                                $uname = $parts[0] ?? '';
                                                $udomain = $parts[1] ?? '';
                                                $maskedEmail = strlen($uname) > 3
                                                    ? substr($uname, 0, 2) . '*****' . substr($uname, -1) . '@' . $udomain
                                                    : substr($uname, 0, 1) . '***@' . $udomain;
                                            @endphp
                                            <code>{{ $maskedEmail }}</code>
                                        </td>
                                        <td>
                                            @if($u->hasVerifiedEmail())
                                                <span class="badge badge-verified px-2 py-1">Verified</span>
                                            @else
                                                <span class="badge badge-unverified px-2 py-1">Unverified</span>
                                                @if(!empty($u->verification_url))
                                                    <a href="{{ $u->verification_url }}" class="btn btn-xs btn-outline-success py-0 px-2 ms-1 small" style="font-size: 0.75rem;">Verifikasi</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $u->email_verified_at ? $u->email_verified_at->format('d M Y H:i:s') : '-' }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('mail.send.welcome') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kirim Welcome">Welcome</button>
                                                </form>
                                                <form action="{{ route('mail.send.markdown') }}" method="POST" class="d-inline ms-1">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kirim Markdown">Markdown</button>
                                                </form>
                                                <form action="{{ route('mail.send.notification') }}" method="POST" class="d-inline ms-1">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kirim Notifikasi">Notif</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada user di database. Silakan klik tombol <strong>+ Registrasi User Baru</strong> di atas untuk mencoba.</p>
                @endif
            </div>

            <!-- Log Viewer -->
            <div class="card card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="fw-bold mb-0">Log Pengiriman Email</h5>
                        <small class="text-muted">Riwayat email yang terkirim pada sistem</small>
                    </div>
                    <form action="{{ route('mail.clear.logs') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Bersihkan Log</button>
                    </form>
                </div>
                <div class="log-box">{{ $recentLogs ?: 'Belum ada log tercatat. Kirim email untuk melihat log.' }}</div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
