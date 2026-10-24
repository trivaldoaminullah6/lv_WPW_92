<x-mail::message>
# Halo {{ $user->name }},

Anda menerima email ini sebagai bagian dari pengujian template **Markdown Mailable** pada praktikum Acara 32.

<x-mail::panel>
{{ $messageText }}
</x-mail::panel>

Berikut adalah rincian akun Anda:
- **Nama:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **Status Verifikasi:** {{ $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum Terverifikasi' }}

<x-mail::button :url="url('/mail')">
Buka Dashboard Praktikum
</x-mail::button>

Terima kasih,<br>
Tim Pengajar WSIWF Polije
</x-mail::message>
