<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TesMail extends Mailable
{
    use Queueable, SerializesModels;

    // modifikasi tambahan username
    public $userName;

    /**
     * Create a new message instance.
     */
    // public function __construct()
    // {
    //     //
    // }

    // modifikasi tambahan username
    public function __construct($userName)
    {
        $this->userName = $userName;
    }

    // tambahan untuk build
    // public function build()
    // {
    //     return $this->subject('Selamat Datang di Aplikasi Kami!')
    //                 ->view('emails.welcome');
    // }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tes Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
