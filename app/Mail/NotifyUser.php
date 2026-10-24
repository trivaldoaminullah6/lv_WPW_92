<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $messageText;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $messageText = 'Ini adalah notifikasi penting dari sistem.')
    {
        $this->user = $user;
        $this->messageText = $messageText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Sistem (Markdown Mail)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notify',
        );
    }

    /**
     * Legacy build method.
     */
    public function build()
    {
        return $this->subject('Pemberitahuan Sistem (Markdown Mail)')
                    ->markdown('emails.notify');
    }
}
