<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\InvoiceMail;
use App\Helpers\NumberToWords;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceController extends Controller
{
    /**
     * Generate invoice preview for a booking
     */
    public function generatePreview($bookingId)
    {
        $booking = Booking::with(['hotel.user', 'user', 'rooms', 'services'])->find($bookingId);
        
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
     * Approve invoice
     */
    public function approve($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services'])->find($invoiceId);
        
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
            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Számla sikeresen jóváhagyva',
                'invoice' => $invoice
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
     * Send invoice to guest
     */
    public function send($invoiceId)
    {
        $invoice = Invoice::with(['booking.hotel.user', 'booking.user', 'booking.rooms', 'booking.services'])->find($invoiceId);
        
        if (!$invoice) {
            return response()->json(['error' => 'Számla nem található'], 404);
        }

        // Check if user is hotel admin
        if ($invoice->booking->hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        if ($invoice->status !== 'approved') {
            return response()->json(['error' => 'A számlát először jóvá kell hagyni'], 400);
        }

        try {
            // Send email with PDF attachment
            Mail::to($invoice->booking->user->email)
                ->send(new InvoiceMail($invoice));

            $invoice->status = 'sent';
            $invoice->sent_at = now();
            $invoice->save();

            return response()->json([
                'message' => 'Számla sikeresen elküldve a vendégnek'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Számla email küldési hiba: ' . $e->getMessage());
            return response()->json([
                'error' => 'Hiba a számla küldésekor',
                'message' => $e->getMessage()
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

        // Check if user is the guest or hotel admin
        $isGuest = $invoice->booking->users_id === auth()->id();
        $isHotelAdmin = $invoice->booking->hotel->user_id === auth()->id();

        if (!$isGuest && !$isHotelAdmin) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        if ($invoice->status === 'draft') {
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
    private function createInvoice(Booking $booking)
    {
        // Calculate invoice details
        $subtotal = $booking->totalPrice;
        $taxRate = 27; // 27% ÁFA
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        // Generate invoice number
        $invoiceNumber = 'SZ' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . '/' . date('Y');

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
    private function generatePDF(Booking $booking, Invoice $invoice)
    {
        // Load all necessary relationships
        $booking->load(['hotel.user', 'user', 'rooms', 'services']);

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
            'guest' => $booking->user
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
