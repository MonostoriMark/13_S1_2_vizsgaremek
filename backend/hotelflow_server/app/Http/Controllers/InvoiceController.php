<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\InvoiceMail;
use App\Helpers\NumberToWords;
use App\Helpers\UrlHelper;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceController extends Controller
{
    /**
     * Generate invoice preview for a booking
     */
    public function generatePreview($bookingId)
    {
        $booking = Booking::with(['hotel.user', 'user', 'rooms', 'services', 'payment', 'invoiceDetails'])->find($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Foglalás nem található'], 404);
        }

        // Check if user is hotel admin for this booking
        if ($booking->hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        // Check if invoice already exists
        $invoice = Invoice::where('booking_id', $bookingId)->first();
        
        if (!$invoice) {
            // Create draft invoice
            $invoice = $this->createInvoice($booking);
        }

        // Generate PDF preview
        $pdfContent = $this->generatePDF($booking, $invoice);
        
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="szamla_elonezet_' . $invoice->invoice_number . '.pdf"');
    }

    /**
     * Preview invoice by invoice ID
     */
    public function preview($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services', 'booking.payment', 'booking.invoiceDetails'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is the guest, hotel admin, or super admin
        $isGuest = $invoice->booking->users_id === auth()->id();
        $isHotelAdmin = $invoice->booking->hotel->user_id === auth()->id();
        $isSuperAdmin = auth()->user()->role === 'super_admin';

        if (!$isGuest && !$isHotelAdmin && !$isSuperAdmin) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        // Generate PDF preview
        $pdfContent = $this->generatePDF($invoice->booking, $invoice);
        
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="szamla_elonezet_' . $invoice->invoice_number . '.pdf"');
    }

    /**
     * Approve invoice
     */
    public function approve($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services', 'booking.payment', 'booking.invoiceDetails'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is hotel admin
        if ($invoice->booking->hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'A számla már jóváhagyva vagy elküldve'], 400);
        }

        DB::beginTransaction();
        try {
            // Generate and save PDF
            $pdfContent = $this->generatePDF($invoice->booking, $invoice);
            $pdfPath = 'invoices/' . $invoice->invoice_number . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdfContent);
            
            $invoice->pdf_path = $pdfPath;
            $invoice->status = 'approved';
            $invoice->approved_at = now();
            
            // Generate payment token for card payments
            $payment = $invoice->booking->payment;
            if ($payment && $payment->method === 'card') {
                $invoice->payment_token = Str::random(64);
            }
            
            $invoice->save();

            // Automatically send invoice after approval for both payment methods
            // Reload invoice with all relationships for email
            $invoice->load(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services', 'booking.payment', 'booking.invoiceDetails']);
            
            // Send appropriate email based on payment method
            try {
                if ($payment && $payment->method === 'card') {
                    // Send invoice email with payment link for card payments
                    Mail::to($invoice->booking->user->email)
                        ->send(new InvoiceMail($invoice));
                } else {
                    // Send invoice email for bank transfer payments
                    Mail::to($invoice->booking->user->email)
                        ->send(new \App\Mail\InvoiceBankTransferMail($invoice));
                }
                
                $invoice->status = 'sent';
                $invoice->sent_at = now();
                $invoice->save();
            } catch (\Exception $mailEx) {
                // Log email error but don't fail the approval
                \Log::error('Számla email küldési hiba (approve): ' . $mailEx->getMessage());
                // Keep status as 'approved' so admin can manually send later
            }

            DB::commit();

            // Determine message based on whether email was sent successfully
            $emailSent = $invoice->status === 'sent';
            $message = $emailSent 
                ? ($payment && $payment->method === 'card' 
                    ? 'Számla jóváhagyva és elküldve a vendégnek fizetési linkkel' 
                    : 'Számla jóváhagyva és elküldve a vendégnek')
                : 'Számla jóváhagyva, de az email küldése sikertelen volt. Kérjük, próbálja meg manuálisan elküldeni.';

            return response()->json([
                'message' => $message,
                'invoice' => $invoice,
                'email_sent' => $emailSent
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hiba a számla jóváhagyásakor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update invoice (only if status is draft)
     */
    public function update(Request $request, $invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is hotel admin
        if ($invoice->booking->hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        // Only allow editing if status is draft
        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'Csak draft státuszú számla szerkeszthető'], 400);
        }

        $request->validate([
            'invoice_number' => 'sometimes|string|max:255|unique:invoices,invoice_number,' . $invoiceId,
            'subtotal' => 'sometimes|numeric|min:0',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'tax_amount' => 'sometimes|numeric|min:0',
            'total_amount' => 'sometimes|numeric|min:0',
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:issue_date',
            'status' => 'sometimes|in:draft,approved,sent',
        ]);

        DB::beginTransaction();
        try {
            // Update invoice number if provided (only for draft)
            if ($request->has('invoice_number')) {
                $invoice->invoice_number = $request->invoice_number;
            }

            // Update status if provided (only for draft)
            if ($request->has('status')) {
                $invoice->status = $request->status;
            }

            // Update subtotal
            if ($request->has('subtotal')) {
                $invoice->subtotal = $request->subtotal;
            }

            // Update tax rate
            if ($request->has('tax_rate')) {
                $invoice->tax_rate = $request->tax_rate;
            }

            // Manual tax amount override (if provided)
            if ($request->has('tax_amount')) {
                $invoice->tax_amount = $request->tax_amount;
            } else if ($request->has('subtotal') || $request->has('tax_rate')) {
                // Auto-calculate if not manually set
                $invoice->tax_amount = round($invoice->subtotal * ($invoice->tax_rate / 100), 2);
            }

            // Manual total amount override (if provided)
            if ($request->has('total_amount')) {
                $invoice->total_amount = $request->total_amount;
            } else if ($request->has('subtotal') || $request->has('tax_amount')) {
                // Auto-calculate if not manually set
                $invoice->total_amount = $invoice->subtotal + $invoice->tax_amount;
            }

            // Update dates
            if ($request->has('issue_date')) {
                $invoice->issue_date = $request->issue_date;
            }
            if ($request->has('due_date')) {
                $invoice->due_date = $request->due_date;
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Számla sikeresen frissítve',
                'invoice' => $invoice->fresh()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hiba a számla frissítésekor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send invoice to guest
     */
    public function send($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services', 'booking.payment', 'booking.invoiceDetails'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is hotel admin
        if ($invoice->booking->hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        if ($invoice->status !== 'approved' && $invoice->status !== 'draft') {
            return response()->json(['error' => 'A számla már elküldve'], 400);
        }

        try {
            // Ensure PDF exists
            if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
                \Log::info('PDF generálása számla küldéshez: ' . $invoice->invoice_number);
                // Generate PDF if missing
                $pdfContent = $this->generatePDF($invoice->booking, $invoice);
                $pdfPath = 'invoices/' . $invoice->invoice_number . '.pdf';
                Storage::disk('public')->put($pdfPath, $pdfContent);
                $invoice->pdf_path = $pdfPath;
                $invoice->save();
            }

            // Reload invoice to ensure all relationships are fresh
            $invoice->refresh();
            $invoice->load(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services', 'booking.payment', 'booking.invoiceDetails']);

            // Send appropriate email based on payment method
            $payment = $invoice->booking->payment;
            $recipientEmail = $invoice->booking->user->email;
            
            \Log::info('Számla email küldése kezdődik', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'recipient_email' => $recipientEmail,
                'payment_method' => $payment ? $payment->method : 'unknown'
            ]);

            if ($payment && $payment->method === 'card') {
                // Ensure payment token exists for card payments
                if (!$invoice->payment_token) {
                    $invoice->payment_token = Str::random(64);
                    $invoice->save();
                }
                // Send invoice email with payment link for card payments
                Mail::to($recipientEmail)
                    ->send(new InvoiceMail($invoice));
                \Log::info('Bankkártyás számla email elküldve', ['invoice_id' => $invoice->id, 'email' => $recipientEmail]);
            } else {
                // Send invoice email for bank transfer payments
                Mail::to($recipientEmail)
                    ->send(new \App\Mail\InvoiceBankTransferMail($invoice));
                \Log::info('Banki átutalásos számla email elküldve', ['invoice_id' => $invoice->id, 'email' => $recipientEmail]);
            }

            $invoice->status = 'sent';
            $invoice->sent_at = now();
            $invoice->save();

            \Log::info('Számla státusz frissítve: sent', ['invoice_id' => $invoice->id]);

            return response()->json([
                'message' => 'Számla sikeresen elküldve a vendégnek',
                'email_sent' => true
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Számla email küldési hiba: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Hiba a számla küldésekor',
                'message' => $e->getMessage(),
                'email_sent' => false
            ], 500);
        }
    }

    /**
     * Download invoice PDF
     */
    public function download($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is the guest, hotel admin, or super admin
        $isGuest = $invoice->booking->users_id === auth()->id();
        $isHotelAdmin = $invoice->booking->hotel->user_id === auth()->id();
        $isSuperAdmin = auth()->user()->role === 'super_admin';

        if (!$isGuest && !$isHotelAdmin && !$isSuperAdmin) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        // Allow super admin to download draft invoices
        if ($invoice->status === 'draft' && !$isSuperAdmin) {
            return response()->json(['error' => 'A számla még nem jóváhagyva'], 400);
        }

        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            // Regenerate PDF if missing
            $pdfContent = $this->generatePDF($invoice->booking, $invoice);
            $pdfPath = 'invoices/' . $invoice->invoice_number . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdfContent);
            $invoice->pdf_path = $pdfPath;
            $invoice->save();
        }

        $pdfContent = Storage::disk('public')->get($invoice->pdf_path);
        
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="szamla_' . $invoice->invoice_number . '.pdf"');
    }

    /**
     * Get invoice by payment token
     */
    public function getByPaymentToken($token)
    {
        try {
            $invoice = Invoice::with([
                'booking.hotel.user', 
                'booking.user', 
                'booking.rooms', 
                'booking.services', 
                'booking.payment'
            ])
            ->where('payment_token', $token)
            ->first();
            
            if (!$invoice) {
                return response()->json(['error' => 'Érvénytelen fizetési link'], 404);
            }

            // Ensure booking is loaded
            if (!$invoice->booking) {
                return response()->json(['error' => 'A foglalás nem található'], 404);
            }

            // Check if invoice is already paid
            if ($invoice->booking->payment && $invoice->booking->payment->status === 'paid') {
                return response()->json(['error' => 'Ez a számla már ki lett fizetve'], 400);
            }

            return response()->json([
                'invoice' => $invoice,
                'booking' => $invoice->booking
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in getByPaymentToken: ' . $e->getMessage());
            return response()->json([
                'error' => 'Hiba történt a számla betöltésekor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for invoice (mark as paid)
     */
    public function processPayment($token)
    {
        $invoice = Invoice::with(['booking.payment', 'booking.user', 'booking.hotel', 'booking.rooms', 'booking.services'])
            ->where('payment_token', $token)
            ->first();
        
        if (!$invoice) {
            return response()->json(['error' => 'Érvénytelen fizetési link'], 404);
        }

        // Check if invoice is already paid
        if ($invoice->booking->payment && $invoice->booking->payment->status === 'paid') {
            return response()->json(['error' => 'Ez a számla már ki lett fizetve'], 400);
        }

        DB::beginTransaction();
        try {
            // Mark payment as paid
            $payment = $invoice->booking->payment;
            if ($payment) {
                $payment->status = 'paid';
                $payment->confirmed_at = now();
                $payment->confirmed_by_user_id = $invoice->booking->users_id;
                $payment->save();
            }

            // Send QR code email to guest after successful payment
            try {
                $booking = $invoice->booking;
                $booking->load(['hotel', 'user', 'rooms.hotel', 'services']);
                
                Mail::to($booking->user->email)
                    ->send(new \App\Mail\BookingConfirmationMail($booking));
            } catch (\Exception $mailEx) {
                \Log::error('QR kód email küldési hiba (payment processed): ' . $mailEx->getMessage());
                // Don't fail the payment if email fails
            }

            DB::commit();

            return response()->json([
                'message' => 'Fizetés sikeresen feldolgozva',
                'invoice' => $invoice->fresh(['booking.payment'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hiba a fizetés feldolgozásakor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice by booking ID
     */
    public function getByBooking($bookingId)
    {
        $booking = Booking::find($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Foglalás nem található'], 404);
        }

        // Check if user is the guest or hotel admin
        $isGuest = $booking->users_id === auth()->id();
        $isHotelAdmin = $booking->hotel->user_id === auth()->id();

        if (!$isGuest && !$isHotelAdmin) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $invoice = Invoice::where('booking_id', $bookingId)->first();
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        return response()->json(['invoice' => $invoice], 200);
    }

    /**
     * Create invoice from booking
     */
    public function createInvoice(Booking $booking)
    {
        // Calculate invoice details
        // For EU sales, VAT can be 0% (tax-free)
        $subtotal = $booking->totalPrice;
        $taxRate = 0; // 0% ÁFA for EU tax-free sales (can be configured per hotel)
        $taxAmount = 0; // Tax-free for EU sales
        $totalAmount = $subtotal; // Total equals subtotal when tax-free

        // Generate invoice number in format: EU2023/00001
        $year = date('Y');
        $prefix = 'EU' . $year . '/';
        
        // Get the last invoice number for this year
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $sequence = (int)$matches[1] + 1;
        } else {
            $sequence = 1;
        }
        
        $invoiceNumber = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Calculate dates
        $issueDate = now()->toDateString();
        $dueDate = now()->addDays(8)->toDateString(); // 8 nap fizetési határidő

        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'tax_rate' => $taxRate,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
        ]);

        return $invoice;
    }

    /**
     * Generate PDF content
     */
    public function generatePDF(Booking $booking, Invoice $invoice)
    {
        // Load all necessary relationships
        $booking->load(['hotel.user', 'user', 'rooms', 'services', 'payment', 'invoiceDetails']);

        // Calculate number of nights
        $startDate = \Carbon\Carbon::parse($booking->startDate);
        $endDate = \Carbon\Carbon::parse($booking->endDate);
        $nights = $startDate->diffInDays($endDate);

        // Prepare invoice items
        $items = [];
        
        // Get total guests count
        $totalGuests = $booking->guests()->count();
        $roomsCount = $booking->rooms()->count();
        $guestsPerRoom = $roomsCount > 0 && $totalGuests > 0 ? ceil($totalGuests / $roomsCount) : 1;
        
        // Calculate services total
        $servicesTotal = $booking->services()->sum('price');
        $roomsTotal = $booking->totalPrice - $servicesTotal;
        
        // Add room items - distribute room total proportionally
        $calculatedRoomsTotal = 0;
        $roomPrices = [];
        
        foreach ($booking->rooms as $room) {
            $guestsCount = $guestsPerRoom;
            // Calculate price per room based on basePrice + pricePerNight * guests * nights
            $calculatedPrice = $room->basePrice + ($room->pricePerNight * $guestsCount * $nights);
            $calculatedRoomsTotal += $calculatedPrice;
            $roomPrices[] = $calculatedPrice;
        }
        
        // Adjust prices to match actual total
        if ($calculatedRoomsTotal > 0 && $roomsTotal > 0) {
            $adjustmentFactor = $roomsTotal / $calculatedRoomsTotal;
            $roomPriceSum = 0;
            foreach ($booking->rooms as $index => $room) {
                $adjustedPrice = round($roomPrices[$index] * $adjustmentFactor, 2);
                $guestsCount = $guestsPerRoom;
                
                // For last room, adjust to match total exactly
                if ($index === count($booking->rooms) - 1) {
                    $adjustedPrice = round($roomsTotal - $roomPriceSum, 2);
                } else {
                    $roomPriceSum += $adjustedPrice;
                }
                
                $items[] = [
                    'name' => $room->name . ' (' . $nights . ' éjszaka' . ($guestsCount > 1 ? ', ' . $guestsCount . ' fő' : '') . ')',
                    'quantity' => 1,
                    'unit_price' => $adjustedPrice,
                    'total' => $adjustedPrice,
                    'type' => 'room'
                ];
            }
        } else {
            // Fallback: divide evenly
            $pricePerRoom = $roomsCount > 0 ? round($roomsTotal / $roomsCount, 2) : $roomsTotal;
            foreach ($booking->rooms as $room) {
                $items[] = [
                    'name' => $room->name . ' (' . $nights . ' éjszaka)',
                    'quantity' => 1,
                    'unit_price' => $pricePerRoom,
                    'total' => $pricePerRoom,
                    'type' => 'room'
                ];
            }
        }

        // Add service items
        foreach ($booking->services as $service) {
            $items[] = [
                'name' => $service->name,
                'quantity' => 1,
                'unit_price' => $service->price,
                'total' => $service->price,
                'type' => 'service'
            ];
        }

        // Render PDF
        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'booking' => $booking,
            'items' => $items,
            'hotel' => $booking->hotel,
            'guest' => $booking->user,
            'invoiceDetails' => $booking->invoiceDetails,
            'payment' => $booking->payment,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
