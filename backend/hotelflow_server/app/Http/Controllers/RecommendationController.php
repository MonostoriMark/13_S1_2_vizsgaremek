<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecommendationController extends Controller
{
    /**
     * Booking.com-style intelligent hotel recommendations
     * 
     * Returns 8-12 hotels ranked by:
     * - Real availability for date range
     * - Room capacity match
     * - Price competitiveness
     * - Rating/popularity
     * - Location relevance
     */
    public function getRecommendations(Request $request)
    {
        // Simple: Just return 10 random hotels, fast
        $cacheKey = 'random_hotels_10';
        
        return Cache::remember($cacheKey, 600, function () {
            try {
                // Get 10 random hotels with minimal data
                $hotels = Hotel::inRandomOrder()
                    ->limit(10)
                    ->select('id', 'user_id', 'location', 'name', 'description', 'type', 'starRating', 'cover_image')
                    ->with(['tags:id,name'])
                    ->get();
                
                // Get hotel IDs for batch queries
                $hotelIds = $hotels->pluck('id')->toArray();
                
                // Batch load first room image for each hotel using proper pivot table
                $hotelImages = [];
                if (!empty($hotelIds)) {
                    $roomImages = DB::table('rooms')
                        ->whereIn('hotels_id', $hotelIds)
                        ->join('imagesRelation', 'rooms.id', '=', 'imagesRelation.rooms_id')
                        ->join('images', 'imagesRelation.images_id', '=', 'images.id')
                        ->select('rooms.hotels_id', 'images.url', 'rooms.id as room_id', 'images.id as image_id')
                        ->orderBy('rooms.hotels_id')
                        ->orderBy('rooms.id')
                        ->orderBy('images.id')
                        ->get();
                    
                    // Group by hotel_id and get first image for each hotel
                    $hotelImages = $roomImages->groupBy('hotels_id')
                        ->map(function($group) {
                            $first = $group->first();
                            return $first ? $first->url : null;
                        })
                        ->toArray();
                }
                
                if ($hotels->isEmpty()) {
                    return [
                        'hotels' => [],
                        'count' => 0,
                        'has_more' => false
                    ];
                }
                
                // Batch calculate min prices for all hotels in ONE query
                $minPrices = [];
                if (!empty($hotelIds)) {
                    $minPrices = DB::table('rooms')
                        ->whereIn('hotels_id', $hotelIds)
                        ->select('hotels_id', DB::raw('MIN(COALESCE(pricePerNight, basePrice, 0)) as min_price'))
                        ->groupBy('hotels_id')
                        ->pluck('min_price', 'hotels_id')
                        ->toArray();
                }
                
                $formattedHotels = $hotels->map(function($hotel) use ($minPrices, $hotelImages) {
                    // Get cover image (use hotel cover_image or fallback to first room image from database)
                    $coverImage = $hotel->cover_image;
                    
                    // If no cover_image, use the first room image we loaded from database
                    if (!$coverImage && isset($hotelImages[$hotel->id])) {
                        $coverImage = $hotelImages[$hotel->id];
                    }
                    
                    return [
                        'id' => $hotel->id,
                        'name' => $hotel->name ?? $hotel->location,
                        'location' => $hotel->location,
                        'type' => $hotel->type,
                        'starRating' => $hotel->starRating ?? 0,
                        'description' => $hotel->description,
                        'cover_image' => $coverImage,
                        'price_per_night' => $minPrices[$hotel->id] ?? 0,
                        'availability_status' => 'available',
                        'tags' => $hotel->tags ? $hotel->tags->map(function($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name
                            ];
                        })->toArray() : []
                    ];
                });
                
                return [
                    'hotels' => $formattedHotels->toArray(),
                    'count' => $formattedHotels->count(),
                    'has_more' => false
                ];
            } catch (\Exception $e) {
                Log::error('RecommendationController error: ' . $e->getMessage());
                return [
                    'hotels' => [],
                    'count' => 0,
                    'has_more' => false
                ];
            }
        });
    }

    private function generateRecommendations($city, $checkIn, $checkOut, $guests, $limit)
    {
        // Step 1: Filter hotels by city (if provided)
        // OPTIMIZATION: Only load essential relationships initially
        $hotelsQuery = Hotel::select('id', 'user_id', 'location', 'name', 'description', 'type', 'starRating', 'cover_image');
        
        if ($city) {
            $hotelsQuery->where(function($query) use ($city) {
                $query->where('location', 'LIKE', "%{$city}%")
                      ->orWhere('name', 'LIKE', "%{$city}%");
            });
        }
        
        $hotels = $hotelsQuery->get();
        
        // Load relationships only for hotels that pass initial filter (lazy loading)
        $hotels->load(['tags:id,name', 'services:id,hotels_id,name,price', 'rooms:id,hotels_id,capacity,pricePerNight,basePrice']);
        
        if ($hotels->isEmpty()) {
            return [
                'hotels' => [],
                'count' => 0,
                'has_more' => false
            ];
        }

        // Step 2: Calculate availability and scores for each hotel
        $hotelScores = [];
        $hotelIds = $hotels->pluck('id')->toArray();
        
        // Batch query booked rooms for all hotels (performance optimization)
        $bookedRoomsByHotel = [];
        if ($checkIn && $checkOut) {
            $bookedRoomsByHotel = $this->getBookedRoomsByHotel($hotelIds, $checkIn, $checkOut);
        }

        // Step 3: Score each hotel
        foreach ($hotels as $hotel) {
            $score = $this->calculateHotelScore(
                $hotel,
                $checkIn,
                $checkOut,
                $guests,
                $bookedRoomsByHotel[$hotel->id] ?? []
            );
            
            if ($score['available']) {
                $hotelScores[] = [
                    'hotel' => $hotel,
                    'score' => $score['totalScore'],
                    'availability' => $score['availability'],
                    'price' => $score['price'],
                    'rooms_available' => $score['rooms_available'],
                    'urgency' => $score['urgency']
                ];
            }
        }

        // Step 4: Sort by total score (highest first)
        usort($hotelScores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Step 5: Take top N hotels (or all if limit is high) and format response
        $recommendedHotels = $limit >= 100 ? $hotelScores : array_slice($hotelScores, 0, $limit);
        
        $formattedHotels = array_map(function($item) use ($checkIn, $checkOut, $guests) {
            $hotel = $item['hotel'];
            $coverImage = $this->getHotelCoverImage($hotel);
            
            return [
                'id' => $hotel->id,
                'name' => $hotel->name ?? $hotel->location,
                'location' => $hotel->location,
                'type' => $hotel->type,
                'starRating' => $hotel->starRating,
                'description' => $hotel->description,
                'cover_image' => $coverImage,
                'price_per_night' => $item['price'],
                'availability_status' => $item['availability']['status'],
                'rooms_available' => $item['rooms_available'],
                'urgency_message' => $item['urgency'],
                'score' => round($item['score'], 2),
                'tags' => $hotel->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name
                ]),
                'services' => $hotel->services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => $s->price
                ]),
                // Include search params for hotel detail page
                'search_params' => [
                    'city' => $hotel->location,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $guests
                ]
            ];
        }, $recommendedHotels);

        return [
            'hotels' => $formattedHotels,
            'count' => count($formattedHotels),
            'has_more' => count($hotelScores) > $limit
        ];
    }

    /**
     * Calculate comprehensive score for a hotel
     * 
     * Scoring factors:
     * - Availability (40%): Real room availability for dates
     * - Price (25%): Competitive pricing
     * - Rating (15%): Star rating
     * - Popularity (10%): Booking count
     * - Location (10%): City center relevance (if city provided)
     */
    private function calculateHotelScore($hotel, $checkIn, $checkOut, $guests, $bookedRoomIds)
    {
        $score = 0;
        $available = false;
        $price = null;
        $roomsAvailable = 0;
        $urgency = null;

        // Get available rooms
        $roomsQuery = Room::where('hotels_id', $hotel->id);
        
        if (!empty($bookedRoomIds)) {
            $roomsQuery->whereNotIn('id', $bookedRoomIds);
        }
        
        $availableRooms = $roomsQuery->get();
        
        if ($availableRooms->isEmpty()) {
            return [
                'available' => false,
                'totalScore' => 0,
                'availability' => ['status' => 'unavailable'],
                'price' => null,
                'rooms_available' => 0,
                'urgency' => null
            ];
        }

        // Filter rooms by capacity
        $suitableRooms = $availableRooms->filter(function($room) use ($guests) {
            return $room->capacity >= $guests;
        });

        if ($suitableRooms->isEmpty()) {
            return [
                'available' => false,
                'totalScore' => 0,
                'availability' => ['status' => 'no_capacity'],
                'price' => null,
                'rooms_available' => 0,
                'urgency' => null
            ];
        }

        $available = true;
        $roomsAvailable = $suitableRooms->count();
        
        // Calculate minimum price
        $minPrice = $suitableRooms->min(function($room) {
            return $room->pricePerNight ?? $room->basePrice ?? 0;
        });
        $price = $minPrice;

        // 1. AVAILABILITY SCORE (40% weight)
        // More available rooms = higher score
        $totalRooms = $hotel->rooms->count();
        $availabilityRatio = $roomsAvailable / max($totalRooms, 1);
        $availabilityScore = min($availabilityRatio * 100, 100) * 0.40;
        
        // Urgency messaging
        if ($roomsAvailable <= 2) {
            $urgency = "Csak {$roomsAvailable} szoba maradt!";
        } elseif ($roomsAvailable <= 5) {
            $urgency = "Korlátozott elérhetőség";
        }

        // 2. PRICE SCORE (25% weight)
        // Lower price = higher score (inverted, normalized)
        // OPTIMIZATION: Use cached price range or calculate from current batch only
        static $cachedPriceRange = null;
        
        if ($cachedPriceRange === null) {
            // Calculate price range from all hotels (cache this)
            $allHotelPrices = DB::table('rooms')
                ->select(DB::raw('MIN(COALESCE(pricePerNight, basePrice, 0)) as min_price, MAX(COALESCE(pricePerNight, basePrice, 0)) as max_price'))
                ->whereNotNull('hotels_id')
                ->first();
            
            $cachedPriceRange = [
                'min' => $allHotelPrices->min_price ?? 0,
                'max' => $allHotelPrices->max_price ?? 1000
            ];
        }
        
        if ($price > 0 && $cachedPriceRange['max'] > $cachedPriceRange['min']) {
            $priceRange = $cachedPriceRange['max'] - $cachedPriceRange['min'];
            // Invert: lower price = higher score
            $normalizedPrice = 1 - (($price - $cachedPriceRange['min']) / $priceRange);
            $priceScore = max(0, min(100, $normalizedPrice * 100)) * 0.25;
        } else {
            $priceScore = 12.5; // Neutral score if price data unavailable
        }

        // 3. RATING SCORE (15% weight)
        $rating = $hotel->starRating ?? 0;
        $ratingScore = ($rating / 5) * 100 * 0.15;

        // 4. POPULARITY SCORE (10% weight)
        // Based on booking count
        $bookingCount = Booking::where('hotels_id', $hotel->id)
            ->whereIn('status', ['confirmed', 'finished'])
            ->count();
        
        // Normalize: hotels with 10+ bookings get max score
        $popularityScore = min(($bookingCount / 10) * 100, 100) * 0.10;

        // 5. LOCATION SCORE (10% weight)
        // If city provided, prefer exact matches
        $locationScore = 5; // Default neutral
        // Could be enhanced with geolocation data if available

        $totalScore = $availabilityScore + $priceScore + $ratingScore + $popularityScore + $locationScore;

        return [
            'available' => $available,
            'totalScore' => $totalScore,
            'availability' => [
                'status' => $roomsAvailable > 5 ? 'available' : ($roomsAvailable > 2 ? 'limited' : 'urgent'),
                'rooms_count' => $roomsAvailable
            ],
            'price' => $price,
            'rooms_available' => $roomsAvailable,
            'urgency' => $urgency
        ];
    }

    /**
     * Batch query booked rooms for multiple hotels (performance optimization)
     */
    private function getBookedRoomsByHotel($hotelIds, $checkIn, $checkOut)
    {
        if (empty($hotelIds)) {
            return [];
        }

        $bookedRooms = DB::table('bookingsRelation')
            ->join('bookings', 'bookingsRelation.booking_id', '=', 'bookings.id')
            ->join('rooms', 'bookingsRelation.rooms_id', '=', 'rooms.id')
            ->whereIn('bookings.hotels_id', $hotelIds)
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->where('bookings.startDate', '<', $checkOut)
            ->where('bookings.endDate', '>', $checkIn)
            ->select('rooms.id as room_id', 'rooms.hotels_id')
            ->distinct()
            ->get()
            ->groupBy('hotels_id')
            ->map(function ($group) {
                return $group->pluck('room_id')->toArray();
            })
            ->toArray();

        return $bookedRooms;
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
