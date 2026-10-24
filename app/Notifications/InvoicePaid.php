<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    public $invoiceNumber;
    public $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $invoiceNumber = 'INV-2024-001', int $amount = 250000)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Konfirmasi Pembayaran: {$this->invoiceNumber}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Pembayaran untuk tagihan {$this->invoiceNumber} telah kami terima.")
            ->line("Jumlah: Rp " . number_format($this->amount, 0, ',', '.'))
            ->action('Lihat Detail Transaksi', url('/mail'))
            ->line('Terima kasih telah menyelesaikan administrasi praktikum tepat waktu!')
            ->salutation('Salam hormat, Bagian Keuangan WSIWF');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_number' => $this->invoiceNumber,
            'amount' => $this->amount,
        ];
    }
}
