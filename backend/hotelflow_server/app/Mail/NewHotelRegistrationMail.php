<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Hotel;
use App\Models\User;

class NewHotelRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;
    public $user;
    public $adminUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Hotel $hotel, User $user)
    {
        $this->hotel = $hotel;
        $this->user = $user;
        $this->adminUrl = \App\Helpers\UrlHelper::getFrontendUrl('/super-admin/hotels');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Új szálloda regisztráció - ' . $this->hotel->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_hotel_registration',
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
