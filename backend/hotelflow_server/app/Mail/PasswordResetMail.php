<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;

    public function __construct($user, $resetUrl)
    {
        $this->user = $user;

        $this->resetUrl = preg_replace(
            '#^https?://[^/]+#',
            'https://hotelflow.optikart.hu',  // ← ide a helyes domain
            $resetUrl
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jelszó visszaállítás - HotelFlow',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}