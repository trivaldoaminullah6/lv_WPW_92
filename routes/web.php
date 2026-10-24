<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\MailTestController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke Acara 31
Route::get('/', function () {
    return redirect()->route('image.index');
});

/*
|--------------------------------------------------------------------------
| ACARA 31: Laravel Image Manipulation
|--------------------------------------------------------------------------
*/
Route::prefix('image')->group(function () {
    Route::get('/', [ImageController::class, 'index'])->name('image.index');
    Route::post('/upload', [ImageController::class, 'upload'])->name('image.upload');
});

/*
|--------------------------------------------------------------------------
| ACARA 32: Laravel Email & Verification & Notification
|--------------------------------------------------------------------------
*/
Route::prefix('mail')->group(function () {
    Route::get('/', [MailTestController::class, 'index'])->name('mail.index');
    Route::get('/register', [MailTestController::class, 'showRegister'])->name('mail.register');
    Route::post('/register', [MailTestController::class, 'register'])->name('mail.register.post');

    // Pengujian pengiriman email
    Route::post('/send-welcome', [MailTestController::class, 'sendWelcome'])->name('mail.send.welcome');
    Route::post('/send-queue', [MailTestController::class, 'sendQueue'])->name('mail.send.queue');
    Route::post('/process-queue', [MailTestController::class, 'processQueue'])->name('mail.process.queue');
    Route::post('/send-markdown', [MailTestController::class, 'sendMarkdown'])->name('mail.send.markdown');
    Route::post('/send-notification', [MailTestController::class, 'sendNotification'])->name('mail.send.notification');
    Route::post('/clear-logs', [MailTestController::class, 'clearLogs'])->name('mail.clear.logs');
});

// Verifikasi Email
Route::get('/email/verify', [MailTestController::class, 'verificationNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [MailTestController::class, 'verificationVerify'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [MailTestController::class, 'verificationSend'])
    ->middleware(['throttle:6,1'])
    ->name('verification.send');
