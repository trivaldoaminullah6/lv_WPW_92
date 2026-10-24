<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acara 31 - Laravel Image Manipulation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .img-preview { max-height: 250px; object-fit: contain; width: 100%; border-radius: 8px; }
        .badge-driver { font-size: 0.85rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Praktikum WSIWF</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link active fw-semibold" href="{{ route('image.index') }}">Acara 31: Image</a>
            <a class="nav-link text-white-50" href="{{ route('mail.index') }}">Acara 32: Email</a>
        </div>
    </div>
</nav>

<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="fw-bold mb-0">Acara 31: Image Manipulation</h2>
                    <p class="text-muted">Intervention Image &middot; Upload &middot; Resize &middot; Crop &middot; Rotate &middot; Watermark</p>
                </div>
                <span class="badge bg-secondary badge-driver py-2 px-3">
                    Engine: <strong>{{ $activeDriver }}</strong>
                </span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Sukses!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong class="d-block mb-1">Gagal Mengunggah:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Upload -->
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3">Unggah Gambar untuk Dimanipulasi</h5>
                <form action="{{ route('image.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="photo" class="form-label">Pilih Berkas Gambar (JPEG, PNG, JPG, WEBP - Max 2MB):</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" required accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Proses & Manipulasi Gambar</button>
                </form>
            </div>

            <!-- Hasil Manipulasi Terakhir -->
            @if(session('latest'))
                @php $latest = session('latest'); @endphp
                <div class="card card-custom p-4 mb-4 bg-white">
                    <h5 class="fw-bold mb-3 text-success">Hasil Pemrosesan Terkini</h5>
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="p-2 border rounded bg-light">
                                <h6 class="fw-bold text-muted">1. Gambar Asli</h6>
                                <img src="{{ asset('uploads/' . $latest['original']) }}" class="img-preview mb-2 bg-white" alt="Original">
                                <small class="text-break text-muted d-block">{{ $latest['original'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded bg-light">
                                <h6 class="fw-bold text-muted">2. Resized (300x300)</h6>
                                <img src="{{ asset('uploads/' . $latest['resized']) }}" class="img-preview mb-2 bg-white" alt="Resized">
                                <small class="text-break text-muted d-block">{{ $latest['resized'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded bg-light">
                                <h6 class="fw-bold text-muted">3. Cropped (200x200)</h6>
                                <img src="{{ asset('uploads/' . $latest['cropped']) }}" class="img-preview mb-2 bg-white" alt="Cropped">
                                <small class="text-break text-muted d-block">{{ $latest['cropped'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="p-2 border rounded bg-light">
                                <h6 class="fw-bold text-muted">4. Rotated (45°)</h6>
                                <img src="{{ asset('uploads/' . $latest['rotated']) }}" class="img-preview mb-2 bg-white" alt="Rotated">
                                <small class="text-break text-muted d-block">{{ $latest['rotated'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="p-2 border rounded bg-light">
                                <h6 class="fw-bold text-muted">5. Watermarked</h6>
                                <img src="{{ asset('uploads/' . $latest['watermark']) }}" class="img-preview mb-2 bg-white" alt="Watermarked">
                                <small class="text-break text-muted d-block">{{ $latest['watermark'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Galeri Semua Berkas di Uploads -->
            <div class="card card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3">Galeri File Hasil Upload</h5>
                @if(count($images) > 0)
                    <div class="row g-3">
                        @foreach($images as $img)
                            <div class="col-6 col-md-3 text-center">
                                <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-between">
                                    <a href="{{ asset('uploads/' . $img) }}" target="_blank">
                                        <img src="{{ asset('uploads/' . $img) }}" class="img-fluid rounded mb-2" style="max-height: 120px; object-fit: cover;" alt="{{ $img }}">
                                    </a>
                                    <span class="small text-truncate text-muted d-block" title="{{ $img }}">{{ $img }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada file di folder uploads. Silakan upload gambar pertama Anda.</p>
                @endif
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
