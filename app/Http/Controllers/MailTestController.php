<?php

namespace App\Http\Controllers;

use App\Mail\NotifyUser;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Notifications\InvoicePaid;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class MailTestController extends Controller
{
    /**
     * Dashboard utama pengujian email Acara 32.
     */
    public function index()
    {
        // Otomatis siapkan tabel jika belum ada
        if (!Schema::hasTable('users') || !Schema::hasTable('jobs')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Abaikan jika migrasi sudah ada
            }
        }

        $users = [];
        $jobCount = 0;
        try {
            $users = User::orderBy('created_at', 'desc')->take(10)->get();
            foreach ($users as $u) {
                if (!$u->hasVerifiedEmail()) {
                    $u->verification_url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'verification.verify',
                        \Illuminate\Support\Carbon::now()->addMinutes(60),
                        [
                            'id' => $u->getKey(),
                            'hash' => sha1($u->getEmailForVerification()),
                        ]
                    );
                }
            }
            $jobCount = DB::table('jobs')->count();
        } catch (\Exception $e) {
            // Jika ada kendala koneksi basis data
        }

        // Baca 50 baris terakhir dari storage/logs/laravel.log
        $logPath = storage_path('logs/laravel.log');
        $recentLogs = '';
        if (File::exists($logPath)) {
            $logContent = File::get($logPath);
            $lines = explode("\n", $logContent);
            $lastLines = array_slice($lines, -60);
            $recentLogs = implode("\n", $lastLines);
        }

        $mailConfig = [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'queue' => config('queue.default'),
        ];

        return view('mail.index', compact('users', 'jobCount', 'recentLogs', 'mailConfig'));
    }

    /**
     * Tampilan form registrasi.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi user + kirim email sambutan + verifikasi email.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);

            // 1. Trigger event Registered (otomatis kirim link verifikasi email karena MustVerifyEmail)
            event(new Registered($user));

            // 2. Kirim WelcomeMail secara langsung
            Mail::to($user->email)->send(new WelcomeMail($user));

            return redirect()->route('verification.notice')
                ->with('success', 'Pendaftaran berhasil! Email selamat datang dan tautan verifikasi telah dikirim.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan user: ' . $e->getMessage()]);
        }
    }

    /**
     * Halaman pemberitahuan belum verifikasi email.
     */
    public function verificationNotice()
    {
        $user = Auth::user() ?? User::latest()->first();
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('mail.index')->with('info', 'Email Anda sudah diverifikasi.');
        }

        $verificationUrl = null;
        if ($user) {
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'verification.verify',
                \Illuminate\Support\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );
        }

        $isLogDriver = config('mail.default') === 'log';

        return view('auth.verify-email', compact('user', 'verificationUrl', 'isLogDriver'));
    }

    /**
     * Resend tautan verifikasi email.
     */
    public function verificationSend(Request $request)
    {
        $user = Auth::user() ?? User::latest()->first();
        if (!$user) {
            return redirect()->route('mail.register')->with('warning', 'Daftarkan user terlebih dahulu.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('mail.index')->with('info', 'Email Anda sudah diverifikasi sebelumnya.');
        }

        try {
            $user->sendEmailVerificationNotification();
            return back()->with('success', 'Tautan verifikasi baru telah berhasil dikirimkan ke email Anda.');
        } catch (\Throwable $e) {
            return back()->with('warning', 'Gagal mengirim email via SMTP: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi link email yang diklik dari email verifikasi.
     */
    public function verificationVerify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Tautan verifikasi tidak valid atau telah kedaluwarsa.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('mail.index')->with('info', 'Email sudah diverifikasi sebelumnya.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('mail.index')->with('success', 'Selamat, email Anda (' . $user->email . ') berhasil diverifikasi!');
    }

    /**
     * Test kirim Welcome Mail secara sinkron (langsung).
     */
    public function sendWelcome(Request $request)
    {
        $user = $this->getTargetUser($request);
        if (!$user) {
            return redirect()->route('mail.index')->with('warning', 'Belum ada user. Daftarkan user baru terlebih dahulu.');
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
            return redirect()->route('mail.index')->with('success', "WelcomeMail berhasil dikirim ke {$user->email} secara sinkron.");
        } catch (\Throwable $e) {
            return redirect()->route('mail.index')->with('warning', "Gagal mengirim WelcomeMail: " . $e->getMessage());
        }
    }

    /**
     * Test kirim Welcome Mail via Queue (antrean database).
     */
    public function sendQueue(Request $request)
    {
        $user = $this->getTargetUser($request);
        if (!$user) {
            return redirect()->route('mail.index')->with('warning', 'Belum ada user. Daftarkan user baru terlebih dahulu.');
        }

        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
            return redirect()->route('mail.index')->with('success', "WelcomeMail telah dimasukkan ke dalam antrean (Queue) untuk {$user->email}.");
        } catch (\Throwable $e) {
            return redirect()->route('mail.index')->with('warning', "Gagal memasukkan ke Queue: " . $e->getMessage());
        }
    }

    /**
     * Proses antrean email secara langsung dari antarmuka web.
     */
    public function processQueue()
    {
        try {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
            ]);
            return redirect()->route('mail.index')->with('success', 'Semua antrean email berhasil diproses dan dikirim.');
        } catch (\Throwable $e) {
            return redirect()->route('mail.index')->with('warning', 'Pemrosesan antrean gagal: ' . $e->getMessage());
        }
    }

    /**
     * Bersihkan log pengiriman email.
     */
    public function clearLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return redirect()->route('mail.index')->with('success', 'Log pengiriman email berhasil dibersihkan.');
    }

    /**
     * Test kirim Markdown Mail (NotifyUser).
     */
    public function sendMarkdown(Request $request)
    {
        $user = $this->getTargetUser($request);
        if (!$user) {
            return redirect()->route('mail.index')->with('warning', 'Belum ada user. Daftarkan user baru terlebih dahulu.');
        }

        try {
            Mail::to($user->email)->send(new NotifyUser($user, 'Ini adalah pesan pengujian komponen template Markdown Laravel.'));
            return redirect()->route('mail.index')->with('success', "Markdown Mail (NotifyUser) berhasil dikirim ke {$user->email}.");
        } catch (\Throwable $e) {
            return redirect()->route('mail.index')->with('warning', "Gagal mengirim Markdown Mail: " . $e->getMessage());
        }
    }

    /**
     * Test kirim Notification (InvoicePaid).
     */
    public function sendNotification(Request $request)
    {
        $user = $this->getTargetUser($request);
        if (!$user) {
            return redirect()->route('mail.index')->with('warning', 'Belum ada user. Daftarkan user baru terlebih dahulu.');
        }

        try {
            $user->notify(new InvoicePaid('INV-2024-' . rand(100, 999), 150000));
            return redirect()->route('mail.index')->with('success', "Notification (InvoicePaid) berhasil dikirim ke {$user->email}.");
        } catch (\Throwable $e) {
            return redirect()->route('mail.index')->with('warning', "Gagal mengirim Notification: " . $e->getMessage());
        }
    }

    /**
     * Dapatkan target user untuk test.
     */
    private function getTargetUser(Request $request)
    {
        if ($request->has('user_id')) {
            return User::find($request->user_id);
        }
        if (Auth::check()) {
            return Auth::user();
        }
        return User::latest()->first();
    }
}
