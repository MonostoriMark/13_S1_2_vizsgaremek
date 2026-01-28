<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HotelDataController extends Controller
{
    /**
     * Get all hotels with rooms in a single optimized response
     * This endpoint provides a complete dataset for client-side filtering/ranking
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllHotelsWithRooms(Request $request)
    {
        // Cache for 10 minutes - hotel/room data doesn't change frequently
        // Invalidate cache when bookings change (handled by cache tags if using Redis)
        $cacheKey = 'all_hotels_with_rooms';
        
        return Cache::remember($cacheKey, 600, function () {
            return $this->buildHotelsData();
        });
    }

    /**
     * Build complete hotels dataset with rooms
     */
    private function buildHotelsData()
    {
        // Load all hotels with essential relationships
        $hotels = Hotel::with([
            'tags:id,name',
            'services:id,hotels_id,name,price',
            'rooms' => function($query) {
                $query->select('id', 'hotels_id', 'name', 'description', 'capacity', 'pricePerNight', 'basePrice')
                      ->with(['images:id,rooms_id,url']);
            }
        ])->get();

        // Get all bookings for availability calculation (batch query)
        $bookings = Booking::whereIn('status', ['pending', 'confirmed'])
            ->select('id', 'hotels_id', 'startDate', 'endDate', 'status')
            ->get();

        // Get room bookings (which rooms are booked in which bookings)
        $roomBookings = DB::table('bookingsRelation')
            ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->select('bookingsRelation.rooms_id', 'bookings.id as booking_id', 'bookings.startDate', 'bookings.endDate')
            ->get()
            ->groupBy('rooms_id');

        // Calculate booking counts per hotel (for popularity score)
        $bookingCounts = Booking::whereIn('status', ['confirmed', 'finished'])
            ->select('hotels_id', DB::raw('COUNT(*) as count'))
            ->groupBy('hotels_id')
            ->pluck('count', 'hotels_id')
            ->toArray();

        // Format hotels data
        $formattedHotels = $hotels->map(function($hotel) use ($bookings, $roomBookings, $bookingCounts) {
            // Get hotel cover image
            $coverImage = $this->getHotelCoverImage($hotel);
            
            // Format rooms
            $formattedRooms = $hotel->rooms->map(function($room) use ($roomBookings) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'capacity' => $room->capacity,
                    'pricePerNight' => $room->pricePerNight ?? $room->basePrice ?? 0,
                    'images' => $room->images->map(fn($img) => [
                        'id' => $img->id,
                        'url' => $img->url
                    ]),
                    // Store which bookings affect this room (for client-side availability)
                    'booked_in_bookings' => $roomBookings[$room->id] ?? []
                ];
            });

            // Calculate min price
            $minPrice = $formattedRooms->where('pricePerNight', '>', 0)->min('pricePerNight') ?? 0;

            return [
                'id' => $hotel->id,
                'name' => $hotel->name ?? $hotel->location,
                'location' => $hotel->location,
                'type' => $hotel->type,
                'starRating' => $hotel->starRating ?? 0,
                'description' => $hotel->description,
                'cover_image' => $coverImage,
                'min_price' => $minPrice,
                'total_rooms' => $formattedRooms->count(),
                'booking_count' => $bookingCounts[$hotel->id] ?? 0,
                'tags' => $hotel->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name
                ]),
                'services' => $hotel->services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => $s->price
                ]),
                'rooms' => $formattedRooms,
                // Include hotel bookings for availability calculation
                'bookings' => $bookings->where('hotels_id', $hotel->id)->map(fn($b) => [
                    'id' => $b->id,
                    'startDate' => $b->startDate,
                    'endDate' => $b->endDate,
                    'status' => $b->status
                ])->values()
            ];
        });

        return response()->json([
            'hotels' => $formattedHotels,
            'count' => $formattedHotels->count(),
            'generated_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Get hotel cover image with fallback
     */
    private function getHotelCoverImage($hotel)
    {
        if ($hotel->cover_image) {
            return $hotel->cover_image;
        }
        
        // Fallback to first room's first image
        $firstRoom = $hotel->rooms->first();
        if ($firstRoom && $firstRoom->images->isNotEmpty()) {
            return $firstRoom->images->first()->url;
        }
        
        return null;
    }
}
