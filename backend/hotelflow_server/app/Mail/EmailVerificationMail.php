<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    public function __construct($user, $verificationUrl)
    {
        $this->user = $user;

        $this->verificationUrl = preg_replace(
            '#^https?://[^/]+#',
            'https://hotelflow.optikart.hu',  // ← ide a helyes domain
            $verificationUrl
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-mail cím megerősítése - HotelFlow',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email_verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}