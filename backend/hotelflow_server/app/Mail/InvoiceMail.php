<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Helpers\UrlHelper;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $paymentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        
        // Generate payment URL if payment token exists (for card payments)
        if ($invoice->payment_token) {
            $this->paymentUrl = UrlHelper::getFrontendUrl('/payment/' . $invoice->payment_token);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Számla - ' . $this->invoice->invoice_number . ' - HotelFlow',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->invoice->pdf_path && Storage::disk('public')->exists($this->invoice->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->invoice->pdf_path)
                ->as('szamla_' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
