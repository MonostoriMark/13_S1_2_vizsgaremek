<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingInvoiceDetail extends Model
{
    protected $table = 'booking_invoice_details';

    protected $fillable = [
        'booking_id',
        'customer_type',
        'full_name',
        'email',
        'company_name',
        'tax_number',
        'country',
        'city',
        'postal_code',
        'address_line',
        'note',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}

