# Alur Pengerjaan Tugas Acara 31 & Acara 32
**Mata Kuliah:** Workshop Sistem Informasi Web Framework (D4 Teknik Informatika, Polije)

---

## 1. Tugas Inti (Ringkasan)

Di kedua acara tidak ada soal tertulis terpisah. Tugasnya adalah **mempraktikkan seluruh Prosedur Kerja (bagian f)**, lalu **menyusun laporan PDF** dari hasil praktik tersebut (bagian g).

| | Acara 31 | Acara 32 |
|---|---|---|
| Topik | Laravel Image Manipulation | Laravel Email |
| Pertemuan | Minggu 9/3 | Minggu 9/4 |
| Alokasi waktu | 100 menit | 100 menit |
| Hasil yang dikumpulkan | Laporan PDF | Laporan PDF |
| Nama file | `ACARA-31_GOL_NIM_NAMA.pdf` | `ACARA-32_GOL_NIM_NAMA.pdf` |
| Contoh nama file | `ACARA-31_A_E1234356_BUDI.pdf` | `ACARA-32_A_E1234356_BUDI.pdf` |
| Dikumpulkan di | https://elearning-jti.polije.ac.id/ | https://elearning-jti.polije.ac.id/ |

**Alat & bahan:** PC/Laptop Windows 10, RAM min. 4 GB, SSD 512 GB, Visual Studio Code, Composer (Acara 32), serta kertas dan pulpen.

---

## 2. Alur Umum (Berlaku untuk Kedua Acara)

1. Siapkan proyek Laravel (boleh pakai proyek yang sama untuk kedua acara).
2. Kerjakan setiap langkah di Prosedur Kerja secara berurutan.
3. **Screenshot** setiap langkah penting: kode, perintah terminal, dan hasil di browser/inbox.
4. Susun laporan PDF di kertas A4.
5. Beri nama file sesuai format, lalu upload ke e-learning **sebelum batas waktu**.

---

## 3. ACARA 31 – Laravel Image Manipulation

### Tujuan
- Instal dan konfigurasi Intervention Image.
- Buat fitur upload gambar dengan validasi.
- Terapkan resize, crop, rotate, watermark.
- Simpan dan tampilkan hasil lewat storage Laravel.

### Langkah Kerja

**Langkah 1 – Instalasi**
```bash
composer require intervention/image
```
- (Opsional, Laravel lama) daftarkan `ImageServiceProvider` di `providers` dan alias `Image` di `aliases` pada `config/app.php`.

**Langkah 2 – Persiapan storage**
```bash
php artisan storage:link
```
Buat juga folder `public/uploads` untuk menyimpan hasil upload.

**Langkah 3 – Buat route, controller, dan view form**
- Route `POST` untuk upload (misal `photo.upload`).
- Controller: `php artisan make:controller ImageController`.
- View Blade berisi form dengan `enctype="multipart/form-data"`, `@csrf`, dan `<input type="file" name="photo">`.
- Tampilkan pesan `session('success')` setelah upload.

**Langkah 4 – Upload + validasi + resize (contoh dari modul)**
```php
use Intervention\Image\Facades\Image;

public function upload(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $file = $request->file('photo');
    $filename = time() . '.' . $file->getClientOriginalExtension();
    Image::make($file)->resize(300, 300)
        ->save(public_path('uploads/' . $filename));

    return back()->with('success', 'Image uploaded and resized successfully.');
}
```

**Langkah 5 – Coba semua teknik manipulasi**

| Teknik | Kode |
|---|---|
| Resize | `Image::make('...')->resize(300, 200)` lalu `->save(...)` |
| Crop | `->crop(200, 200, 100, 100)` |
| Watermark | `->insert('watermark.png', 'bottom-right', 10, 10)` |
| Rotate | `->rotate(45)` |
| Ubah format | `->encode('png')` |
| Tampil langsung | `return Image::make('image.jpg')->response('jpg');` |
| Atur kualitas | `$image->save('image.jpg', 75);` |

**Langkah 6 – Tampilkan hasil**
Tampilkan gambar hasil manipulasi di halaman Blade (`<img src="{{ asset('uploads/'.$filename) }}">`).

### Checklist Acara 31
- [ ] Intervention Image terinstal
- [ ] Form upload jalan, validasi bekerja (coba file salah format / > 2 MB)
- [ ] Resize berhasil
- [ ] Crop berhasil
- [ ] Rotate berhasil
- [ ] Watermark berhasil
- [ ] Hasil tersimpan dan tampil di browser
- [ ] Screenshot semua langkah

### ⚠️ Catatan Versi
Kode modul memakai `Image::make()` (Intervention Image **v2**). Jika `composer require` memasang **v3**, sintaks berubah (memakai `ImageManager`, `read()`, `scale()/resize()`, `toPng()`), dan facade `Image::make` tidak ada lagi. Dua solusi:
- Pasang v2: `composer require intervention/image:^2.7`, atau
- Pakai sintaks v3 dan tulis di laporan alasannya.

---

## 4. ACARA 32 – Laravel Email

### Tujuan
- Memahami konfigurasi mail server.
- Menggunakan class `Mailable`.
- Implementasi verifikasi email.
- Integrasi kirim email ke proses registrasi user.

### Langkah Kerja

**Langkah 1 – Konfigurasi `.env`** (contoh memakai Mailtrap)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin@example.com
MAIL_FROM_NAME="${APP_NAME}"
```
Daftar akun Mailtrap (atau pakai Mailhog) dan salin kredensialnya. Cek juga `config/mail.php`.

**Langkah 2 – Buat Mailable**
```bash
php artisan make:mail WelcomeMail
```
- Isi `WelcomeMail.php` (constructor menerima `$user`, subject `Selamat Datang!`, view `emails.welcome`).
- Buat view `resources/views/emails/welcome.blade.php`.

**Langkah 3 – Kirim email dari controller (saat registrasi)**
```php
$user = User::create([...]);
Mail::to($user->email)->send(new WelcomeMail($user));
return response()->json(['message' => 'Email dikirim!']);
```

**Langkah 4 – Verifikasi email**
- Pada model `User` tambahkan `implements MustVerifyEmail`.
- Aktifkan route verifikasi (`Auth::routes(['verify' => true]);`) atau pakai middleware `['auth', 'verified']` pada route yang dilindungi.
- Daftar akun baru dan pastikan link verifikasi masuk ke inbox.

**Langkah 5 – Resend email verifikasi**
Buat route `POST /email/verification-notification` bernama `verification.send` dengan middleware `['auth', 'throttle:6,1']`.

**Langkah 6 – Queue (disarankan)**
- `.env`: `QUEUE_CONNECTION=database`
- Kirim dengan `Mail::to(...)->queue(new WelcomeMail($user));`
- Jalankan: `php artisan queue:table`, `php artisan migrate`, lalu `php artisan queue:work`.

**Langkah 7 – Template Markdown**
```bash
php artisan make:mail NotifyUser --markdown=emails.notify
```
Ubah di Mailable: `return $this->markdown('emails.notify');` dan isi view dengan komponen `@component('mail::message')` dan `mail::button`.

**Langkah 8 – Notification (cara lain)**
```bash
php artisan make:notification InvoicePaid
```
Isi `via()` = `['mail']` dan `toMail()` dengan `MailMessage`, lalu kirim `$user->notify(new InvoicePaid());`.

**Langkah 9 – Testing & debugging**
- Cek hasil di inbox Mailtrap/Mailhog.
- Alternatif log: `MAIL_MAILER=log` (email tercatat di `storage/logs`).

**Langkah 10 – Tambahan (baca & coba)**
Tutorial SMTP Gmail: https://qadrlabs.com/post/tutorial-laravel-kirim-email-menggunakan-smtp-gmail
(Gmail memerlukan **App Password**, bukan password akun biasa.)

### Checklist Acara 32
- [ ] `.env` mail terkonfigurasi
- [ ] `WelcomeMail` + view dibuat
- [ ] Email welcome terkirim saat registrasi (bukti: inbox Mailtrap)
- [ ] `MustVerifyEmail` aktif, email verifikasi masuk
- [ ] Resend verifikasi berfungsi
- [ ] Queue dicoba
- [ ] Template Markdown dicoba
- [ ] Notification dicoba
- [ ] Screenshot semua langkah

### ⚠️ Catatan Versi
Modul memakai gaya `build()` pada Mailable (Laravel ≤ 10). Di Laravel 11/12, `make:mail` menghasilkan `envelope()` dan `content()`. Sesuaikan dengan versi proyek Anda. Host Mailtrap yang baru juga biasanya `sandbox.smtp.mailtrap.io`, salin dari dashboard.

---

## 5. Menyusun Laporan (Untuk Masing-masing Acara)

**Format:** kertas A4, file `*.pdf`.

**Struktur wajib:**
1. **Cover**: judul acara, identitas diri (**NIM, Nama, Golongan**), tugas Minggu ke-X, Politeknik Negeri Jember / D4 Teknik Informatika.
2. **Pendahuluan**: latar belakang singkat + tujuan + teori dasar (ringkas dengan kata sendiri).
3. **Hasil Praktik**: tiap langkah berisi potongan kode, screenshot hasil, dan penjelasan singkat.
4. **Kesimpulan**: apa yang dipelajari dan dicapai (sesuai 4 CPMK).
5. **Daftar Pustaka**: modul praktikum, dokumentasi Laravel, Intervention Image, dsb.

**Penamaan file:** `ACARA-X_GOL_NIM_NAMA.pdf`

---

## 6. Penilaian

Skor 1–4 berdasarkan **ketepatan waktu** dan **ketepatan langkah kerja**; **Total = Skor × 25** (maksimal 100).

| Skor | Kriteria |
|---|---|
| 4 | Tepat waktu dan langkah kerja tepat |
| 3 | Tepat waktu, langkah kerja kurang tepat |
| 2 | Terlambat dan langkah kerja kurang tepat |
| 1 | Terlambat dan langkah kerja tidak sesuai |

---

## 7. Urutan Kerja yang Disarankan

1. Kerjakan **Acara 31** sampai selesai, ambil screenshot sambil jalan.
2. Tulis laporan Acara 31, ekspor ke PDF, upload.
3. Kerjakan **Acara 32**, dengan cara yang sama.
4. Tulis laporan Acara 32, ekspor ke PDF, upload.
5. Periksa ulang nama file dan identitas sebelum submit.