<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceTag;
use Illuminate\Support\Facades\DB;

class ServiceTagController extends Controller
{
    /**
     * Get all tags (for admin panel and search filters)
     */
    public function index()
    {
        $tags = ServiceTag::with('user')->orderBy('name')->get();
        return response()->json($tags);
    }

    /**
     * Create a new tag (shared/common tag that can be used by any hotel)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:serviceTags,name'
        ]);

        $tag = ServiceTag::create([
            'name' => $validated['name'],
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag->load('user')
        ], 201);
    }

    /**
     * Get tag usage information (which tags are used on hotels vs rooms)
     * Returns detailed information about which hotels/rooms use each tag
     */
    public function getUsage()
    {
        $tags = ServiceTag::all();
        $usage = [];

        foreach ($tags as $tag) {
            // Get hotels using this tag
            $hotelIds = DB::table('hotelTagRelation')
                ->where('serviceTags_id', $tag->id)
                ->pluck('hotels_id')
                ->toArray();

            $hotels = DB::table('hotels')
                ->whereIn('id', $hotelIds)
                ->select('id', 'name', 'location')
                ->get()
                ->toArray();

            // Get rooms using this tag
            $roomIds = DB::table('roomTagRelation')
                ->where('serviceTags_id', $tag->id)
                ->pluck('rooms_id')
                ->toArray();

            $rooms = DB::table('rooms')
                ->whereIn('id', $roomIds)
                ->select('id', 'name', 'hotels_id')
                ->get()
                ->toArray();

            $usage[$tag->id] = [
                'is_used' => !empty($hotelIds) || !empty($roomIds),
                'hotels' => $hotels,
                'rooms' => $rooms,
                'hotel_count' => count($hotelIds),
                'room_count' => count($roomIds)
            ];
        }

        return response()->json([
            'usage' => $usage,
            'hotel_tags' => array_keys(array_filter($usage, fn($u) => $u['hotel_count'] > 0)),
            'room_tags' => array_keys(array_filter($usage, fn($u) => $u['room_count'] > 0))
        ]);
    }

    /**
     * Update a tag
     */
    public function update(Request $request, $id)
    {
        $tag = ServiceTag::find($id);
        
        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:serviceTags,name,' . $id
        ]);

        $tag->update([
            'name' => $validated['name']
        ]);

        return response()->json([
            'message' => 'Tag updated successfully',
            'tag' => $tag
        ]);
    }

    /**
     * Delete a tag (only if created by current user and not in use)
     */
    public function destroy($id)
    {
        $tag = ServiceTag::find($id);
        
        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found'
            ], 404);
        }

        // Check if user is the creator
        if ($tag->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Csak a saját címkéit törölheti',
                'forbidden' => true
            ], 403);
        }

        // Check if tag is used on hotels
        $usedOnHotels = DB::table('hotelTagRelation')
            ->where('serviceTags_id', $id)
            ->exists();

        // Check if tag is used on rooms
        $usedOnRooms = DB::table('roomTagRelation')
            ->where('serviceTags_id', $id)
            ->exists();

        if ($usedOnHotels || $usedOnRooms) {
            return response()->json([
                'message' => 'A használatban lévő címke nem törölhető. Először távolítsa el az összes szállodától és szobától.',
                'in_use' => true
            ], 422);
        }

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully'
        ]);
    }
}
