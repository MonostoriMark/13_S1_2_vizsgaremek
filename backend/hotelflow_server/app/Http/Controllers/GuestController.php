<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    // Vendégek hozzáadása foglaláshoz
    public function store(Request $request, $bookingId)
    {
        $request->validate([
            'guests' => 'required|array|min:1',
            'guests.*.name' => 'required|string',
            'guests.*.idNumber' => 'required|string',
            'guests.*.dateOfBirth' => 'required|date'
        ]);

        $booking = Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['error' => 'Foglalás nem található'], 404);
        }

        // Összes szoba kapacitása
        $totalCapacity = $booking->rooms->sum('capacity');
        if (count($request->guests) > $totalCapacity) {
            return response()->json(['error' => 'Túl sok vendég a foglaláshoz képest'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->guests as $guestData) {
                $booking->guests()->create([
                    'name' => $guestData['name'],
                    'idNumber' => $guestData['idNumber'],
                    'dateOfBirth' => $guestData['dateOfBirth']
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Vendégek sikeresen hozzáadva'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hiba a vendégek hozzáadásakor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
