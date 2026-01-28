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
        })->with(['tags', 'services', 'rooms.images'])->get();

        if ($hotels->isEmpty()) {
            return response()->json([]);
        }

        // OPTIMIZATION: Batch query all booked rooms for all hotels at once
        // Get all hotel IDs first
        $hotelIds = $hotels->pluck('id')->toArray();
        
        // Single query to get all booked room IDs for the date range, grouped by hotel
        $bookedRoomsByHotel = DB::table('bookingsRelation')
            ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
            ->join('rooms', 'bookingsRelation.rooms_id', '=', 'rooms.id')
            ->whereIn('bookings.hotels_id', $hotelIds)
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->where('bookings.startDate', '<', $endDate)
            ->where('bookings.endDate', '>', $startDate)
            ->select('rooms.id as room_id', 'rooms.hotels_id')
            ->distinct()
            ->get()
            ->groupBy('hotels_id')
            ->map(function ($group) {
                return $group->pluck('room_id')->toArray();
            })
            ->toArray();

        $results = [];

        foreach ($hotels as $hotel) {
            // Get booked room IDs for this specific hotel (empty array if none)
            $bookedRoomIds = $bookedRoomsByHotel[$hotel->id] ?? [];

            // Query available rooms for this hotel
            $roomsQuery = Room::where('hotels_id', $hotel->id);
            
            if (!empty($bookedRoomIds)) {
                $roomsQuery->whereNotIn('id', $bookedRoomIds);
            }
            
            $rooms = $roomsQuery->with(['tags', 'images'])
                ->get();

            if ($rooms->isEmpty()) {
                continue;
            }

            // Foglalási tervek generálása (optimized algorithm)
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
                'cover_image' => $hotel->cover_image,
                'description' => $hotel->description,

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

                // Include rooms with images for image fallback
                'rooms' => $hotel->rooms->map(fn ($room) => [
                    'id' => $room->id,
                    'images' => $room->images->map(fn ($img) => [
                        'id' => $img->id,
                        'url' => $img->url
                    ])
                ]),

                'plans' => $plans
            ];
        }

        return response()->json($results);
    }

    /**
     * Optimized plan generation using dynamic programming approach
     * Generates plans more efficiently than exponential backtracking
     */
    private function generatePlans($rooms, $guests, $nights)
    {
        if ($rooms->isEmpty() || $guests <= 0) {
            return collect([]);
        }

        // Group rooms by type (same capacity and price) for better optimization
        $roomGroups = $rooms->groupBy(function ($room) {
            return $room->capacity . '-' . $room->pricePerNight;
        })->map(function ($group) {
            return [
                'room' => $group->first(),
                'count' => $group->count(),
                'capacity' => $group->first()->capacity,
                'pricePerNight' => $group->first()->pricePerNight
            ];
        })->values();

        // Use dynamic programming to find optimal plans
        $plans = $this->findOptimalPlans($roomGroups, $guests, $nights);

        // If DP didn't find enough, fall back to greedy approach
        if ($plans->count() < 3) {
            $greedyPlans = $this->findGreedyPlans($rooms, $guests, $nights);
            $plans = $plans->merge($greedyPlans)->unique(function ($plan) {
                // Create unique key based on room IDs and total price
                $roomIds = collect($plan['rooms'])->pluck('room_id')->sort()->implode('-');
                return $roomIds . '-' . $plan['total_price'];
            });
        }

        // Sort and select best plans
        $sortedPlans = $plans
            ->sortBy([
                ['total_price', 'asc'],  // Cheapest first
                ['room_count', 'asc'],   // Fewer rooms preferred
            ])
            ->values();

        // Find best plans: cheapest, best value (price per guest), and alternative
        $bestPlans = collect([]);
        
        // 1. Cheapest plan (absolute lowest price)
        if ($sortedPlans->isNotEmpty()) {
            $cheapest = $sortedPlans->first();
            $cheapest['label'] = 'Legolcsóbb';
            $bestPlans->push($cheapest);
        }

        // 2. Best value plan (lowest price per guest, but not necessarily cheapest)
        $bestValue = $sortedPlans
            ->map(function ($plan) use ($guests) {
                $totalCapacity = collect($plan['rooms'])->sum('capacity');
                $plan['price_per_guest'] = $plan['total_price'] / max($totalCapacity, $guests);
                return $plan;
            })
            ->sortBy('price_per_guest')
            ->first();

        if ($bestValue) {
            // Check if this plan is already in bestPlans
            $isDuplicate = $bestPlans->contains(function ($bp) use ($bestValue) {
                return $bp['total_price'] === $bestValue['total_price'] && 
                       $bp['room_count'] === $bestValue['room_count'];
            });
            
            if (!$isDuplicate) {
                $bestValue['label'] = 'Ajánlott';
                // Remove price_per_guest from final output
                unset($bestValue['price_per_guest']);
                $bestPlans->push($bestValue);
            }
        }

        // 3. Alternative plan (different from the first two)
        $alternative = $sortedPlans
            ->reject(function ($plan) use ($bestPlans) {
                return $bestPlans->contains(function ($bp) use ($plan) {
                    return $bp['total_price'] === $plan['total_price'] && 
                           $bp['room_count'] === $plan['room_count'];
                });
            })
            ->first();

        if ($alternative) {
            $alternative['label'] = 'Alternatíva';
            $bestPlans->push($alternative);
        }

        // Ensure we have at least one plan labeled as "Ajánlott" if we have any plans
        if ($bestPlans->isNotEmpty() && !$bestPlans->contains('label', 'Ajánlott')) {
            $bestPlans->first()['label'] = 'Ajánlott';
        }

        return $bestPlans->take(3)->values();
    }

    /**
     * Dynamic programming approach for finding optimal room combinations
     */
    private function findOptimalPlans($roomGroups, $guests, $nights)
    {
        $plans = collect([]);
        $maxRooms = min(5, ceil($guests / ($roomGroups->min('capacity') ?: 1))); // Limit search space

        // Try combinations with increasing number of rooms
        for ($roomCount = 1; $roomCount <= $maxRooms && $plans->count() < 10; $roomCount++) {
            $combinations = $this->generateCombinations($roomGroups, $roomCount, $guests, $nights);
            $plans = $plans->merge($combinations);
        }

        return $plans;
    }

    /**
     * Generate room combinations for a specific room count
     */
    private function generateCombinations($roomGroups, $roomCount, $guests, $nights)
    {
        $plans = collect([]);
        
        // Use recursive function to generate combinations
        $this->combineRooms($roomGroups, $roomCount, $guests, $nights, [], 0, $plans);
        
        return $plans;
    }

    private function combineRooms($roomGroups, $targetCount, $guests, $nights, $current, $startIndex, &$plans)
    {
        $currentCount = collect($current)->sum('count');
        $currentCapacity = collect($current)->sum(function ($item) {
            return $item['count'] * $item['capacity'];
        });

        // Base case: we have enough rooms
        if ($currentCount === $targetCount) {
            if ($currentCapacity >= $guests) {
                $rooms = collect($current)->flatMap(function ($item) {
                    return collect(range(0, $item['count'] - 1))->map(fn() => $item['room']);
                });
                $plans->push($this->buildPlan($rooms, $nights));
            }
            return;
        }

        // Base case: can't add more rooms
        if ($currentCount >= $targetCount || $startIndex >= $roomGroups->count()) {
            return;
        }

        // Try adding 0 to available count of current room group
        $group = $roomGroups[$startIndex];
        $maxToAdd = min($targetCount - $currentCount, $group['count']);

        for ($add = 0; $add <= $maxToAdd; $add++) {
            if ($add > 0) {
                $newCurrent = $current;
                $newCurrent[] = [
                    'room' => $group['room'],
                    'count' => $add,
                    'capacity' => $group['capacity'],
                    'pricePerNight' => $group['pricePerNight']
                ];
                $this->combineRooms($roomGroups, $targetCount, $guests, $nights, $newCurrent, $startIndex + 1, $plans);
            } else {
                $this->combineRooms($roomGroups, $targetCount, $guests, $nights, $current, $startIndex + 1, $plans);
            }
        }
    }

    /**
     * Greedy approach: find plans by trying different strategies
     */
    private function findGreedyPlans($rooms, $guests, $nights)
    {
        $plans = collect([]);
        
        // Strategy 1: Sort by capacity descending (fit most guests per room)
        $roomsByCapacity = $rooms->sortByDesc('capacity')->values();
        $plan1 = $this->greedySelect($roomsByCapacity, $guests, $nights);
        if ($plan1) {
            $plans->push($plan1);
        }

        // Strategy 2: Sort by price ascending (cheapest first)
        $roomsByPrice = $rooms->sortBy('pricePerNight')->values();
        $plan2 = $this->greedySelect($roomsByPrice, $guests, $nights);
        if ($plan2 && !$plans->contains(function ($p) use ($plan2) {
            return $p['total_price'] === $plan2['total_price'] && $p['room_count'] === $plan2['room_count'];
        })) {
            $plans->push($plan2);
        }

        // Strategy 3: Sort by value (capacity/price ratio)
        $roomsByValue = $rooms->sortByDesc(function ($room) {
            return $room->capacity / max($room->pricePerNight, 1);
        })->values();
        $plan3 = $this->greedySelect($roomsByValue, $guests, $nights);
        if ($plan3 && !$plans->contains(function ($p) use ($plan3) {
            return $p['total_price'] === $plan3['total_price'] && $p['room_count'] === $plan3['room_count'];
        })) {
            $plans->push($plan3);
        }

        return $plans;
    }

    /**
     * Greedy selection: pick rooms one by one until capacity is met
     */
    private function greedySelect($rooms, $guests, $nights)
    {
        $selected = collect([]);
        $capacity = 0;

        foreach ($rooms as $room) {
            if ($capacity >= $guests) {
                break;
            }
            $selected->push($room);
            $capacity += $room->capacity;
        }

        if ($capacity >= $guests) {
            return $this->buildPlan($selected, $nights);
        }

        return null;
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
                'tags' => $room->tags->pluck('name'),
                
                // ROOM IMAGES
                'images' => $room->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->url
                ])
            ];
        });

        return [
            'room_count'   => count($rooms),
            'total_price' => $total,
            'rooms'       => $roomData
        ];
    }
}
