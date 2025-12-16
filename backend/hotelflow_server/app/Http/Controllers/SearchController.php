<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SearchController extends Controller
{
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

        // Hotel szűrés város alapján
        $hotels = Hotel::where('location', 'LIKE', "%{$city}%")->get();

        $results = [];

        foreach ($hotels as $hotel) {

            // Elérhető szobák a foglalások alapján
            $rooms = Room::where('hotels_id', $hotel->id)
                ->whereDoesntHave('bookings', function ($q) use ($startDate, $endDate) {
                    $q->whereIn('status', ['pending', 'confirmed'])
                      ->where('startDate', '<', $endDate)
                      ->where('endDate',   '>', $startDate);
                })
                ->with('tags') // ROOM TAGS
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
                'location'   => $hotel->location,
                'type'       => $hotel->type,
                'starRating' => $hotel->starRating,

                // HOTEL TAGS
                'tags' => $hotel->tags->pluck('name'),

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
        $capacity = collect($current)->sum('capacity');

        if ($capacity >= $guests) {
            $plans[] = $this->buildPlan($current, $nights);
            return;
        }

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
