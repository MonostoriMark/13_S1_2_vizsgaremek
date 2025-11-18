<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // Foglalás létrehozása
    public function store(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'exists:rooms,id',
            'services' => 'array',
            'services.*' => 'exists:services,id'
        ]);

        DB::beginTransaction();
        try {
            // Foglalás létrehozása
            $booking = Booking::create([
                'users_id' => $request->userId,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'totalPrice' => 0, // Később kalkulálható
            ]);

            // Szobák hozzárendelése
            $booking->rooms()->sync($request->rooms);

            // Szolgáltatások hozzárendelése
            if ($request->has('services')) {
                $booking->services()->sync($request->services);
            }

            DB::commit();
            return response()->json(['bookingId' => $booking->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hiba a foglalás létrehozásakor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
