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
        $tags = ServiceTag::orderBy('name')->get();
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
            'name' => $validated['name']
        ]);

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag
        ], 201);
    }

    /**
     * Get tag usage information (which tags are used on hotels vs rooms)
     * Helps frontend show which tags are available for hotels vs rooms
     */
    public function getUsage()
    {
        // Get tags used on hotels
        $hotelTags = DB::table('hotelTagRelation')
            ->distinct()
            ->pluck('serviceTags_id')
            ->toArray();

        // Get tags used on rooms
        $roomTags = DB::table('roomTagRelation')
            ->distinct()
            ->pluck('serviceTags_id')
            ->toArray();

        return response()->json([
            'hotel_tags' => $hotelTags,
            'room_tags' => $roomTags
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
     * Delete a tag (only if not in use)
     */
    public function destroy($id)
    {
        $tag = ServiceTag::find($id);
        
        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found'
            ], 404);
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
                'message' => 'Cannot delete tag that is currently in use. Remove it from all hotels/rooms first.',
                'in_use' => true
            ], 422);
        }

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully'
        ]);
    }
}
