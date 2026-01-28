<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'tax_rate',
        'issue_date',
        'due_date',
        'pdf_path',
        'approved_at',
        'sent_at'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
