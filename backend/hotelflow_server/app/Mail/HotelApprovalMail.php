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

class HotelApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;
    public $user;
    public $isApproved;
    public $adminUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Hotel $hotel, User $user, bool $isApproved)
    {
        $this->hotel = $hotel;
        $this->user = $user;
        $this->isApproved = $isApproved;
        $this->adminUrl = \App\Helpers\UrlHelper::getFrontendUrl('/admin');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isApproved 
            ? 'Szállodája jóváhagyva - ' . $this->hotel->name
            : 'Szállodája elutasítva - ' . $this->hotel->name;
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.hotel_approval',
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
