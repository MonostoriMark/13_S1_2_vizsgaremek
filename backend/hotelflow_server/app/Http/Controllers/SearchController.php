<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function getLocations()
    {
        $locations = Hotel::select('location')
            ->distinct()
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->orderBy('location')
            ->pluck('location');

        return response()->json($locations);
    }

    public function searchWithPlans(Request $request)
    {
        // GET query paraméterek
        $city      = $request->query('city');
        $startDate = $request->query('startDate');
        $endDate   = $request->query('endDate');
        $guests    = (int) $request->query('guests');

        // Validáció
        $request->validate([
            'city'      => 'required|string',
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after:startDate',
            'guests'    => 'required|integer|min:1',
        ]);

        $nights = Carbon::parse($startDate)->diffInDays($endDate);

        // Hotel szűrés név vagy location alapján
        $hotels = Hotel::where(function($query) use ($city) {
            $query->where('name', 'LIKE', "%{$city}%")
                  ->orWhere('location', 'LIKE', "%{$city}%");
        })->with(['tags', 'services'])->get();

        $results = [];

        foreach ($hotels as $hotel) {

            // Elérhető szobák a foglalások alapján
            // Use direct query to avoid relationship caching issues and ensure fresh data
            $bookedRoomIds = DB::table('bookingsRelation')
                ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
                ->whereIn('bookings.status', ['pending', 'confirmed'])
                ->where('bookings.startDate', '<', $endDate)
                ->where('bookings.endDate', '>', $startDate)
                ->distinct()
                ->pluck('bookingsRelation.rooms_id')
                ->toArray();

            // Query rooms - exclude booked ones if any exist
            $roomsQuery = Room::where('hotels_id', $hotel->id);
            
            if (!empty($bookedRoomIds)) {
                $roomsQuery->whereNotIn('id', $bookedRoomIds);
            }
            
            $rooms = $roomsQuery->with('tags') // ROOM TAGS
                ->get();

            if ($rooms->isEmpty()) {
                continue;
            }

            // Foglalási tervek generálása
            $plans = $this->generatePlans($rooms, $guests, $nights);

            if ($plans->isEmpty()) {
                continue;
            }

            $results[] = [
                'hotel_id'   => $hotel->id,
                'name'       => $hotel->name,
                'location'   => $hotel->location,
                'type'       => $hotel->type,
                'starRating' => $hotel->starRating,

                // HOTEL TAGS (return full tag objects)
                'tags' => $hotel->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name
                ]),

                // IGÉNYBE VEHETŐ SZOLGÁLTATÁSOK
                'services' => $hotel->services->map(fn ($s) => [
                    'id'    => $s->id,
                    'name'  => $s->name,
                    'price' => $s->price
                ]),

                'plans' => $plans
            ];
        }

        return response()->json($results);
    }

    private function generatePlans($rooms, $guests, $nights)
    {
        $plans = [];

        $rooms = $rooms->sortByDesc('capacity')->values();

        $this->backtrack($rooms, $guests, $nights, [], 0, $plans);

        return collect($plans)
            ->unique(fn ($p) => $p['total_price'] . '-' . $p['room_count'])
            ->sortBy([
                ['room_count', 'asc'],
                ['total_price', 'asc'],
            ])
            ->values()
            ->take(3)
            ->map(function ($plan, $i) {
                $plan['label'] = match ($i) {
                    0 => 'Ajánlott',
                    1 => 'Legolcsóbb',
                    default => 'Alternatíva'
                };
                return $plan;
            });
    }

    private function backtrack($rooms, $guests, $nights, $current, $index, &$plans)
    {
        // Base case: no more rooms to try
        if ($index >= $rooms->count()) {
            return;
        }

        $capacity = collect($current)->sum('capacity');

        if ($capacity >= $guests) {
            $plans[] = $this->buildPlan($current, $nights);
            return;
        }

        // Try adding each remaining room
        for ($i = $index; $i < $rooms->count(); $i++) {
            $this->backtrack(
                $rooms,
                $guests,
                $nights,
                array_merge($current, [$rooms[$i]]),
                $i + 1,
                $plans
            );
        }
    }

    private function buildPlan($rooms, $nights)
    {
        $total = 0;

        $roomData = collect($rooms)->map(function ($room) use ($nights, &$total) {
            $price = $room->pricePerNight * $nights;
            $total += $price;

            return [
                'room_id'  => $room->id,
                'name'     => $room->name,
                'capacity' => $room->capacity,
                'price'    => $price,

                // ROOM TAGS
                'tags' => $room->tags->pluck('name')
            ];
        });

        return [
            'room_count'   => count($rooms),
            'total_price' => $total,
            'rooms'       => $roomData
        ];
    }
}
