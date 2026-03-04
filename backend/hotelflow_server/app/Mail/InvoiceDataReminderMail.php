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

class InvoiceDataReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;
    public $user;
    public $companyInfoUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Hotel $hotel, User $user)
    {
        $this->hotel = $hotel;
        $this->user = $user;
        $this->companyInfoUrl = \App\Helpers\UrlHelper::getFrontendUrl('/admin/company-info');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Fontos: Számlázási adatok kitöltése - ' . $this->hotel->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_data_reminder',
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
