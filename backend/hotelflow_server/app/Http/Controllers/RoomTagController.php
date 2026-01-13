<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;;
use App\Models\ServiceTag;

class RoomTagController extends Controller
{
    public function store(Request $request, Room $room)
    {
        // 🔐 szoba → hotel → user
        if ($room->hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nincs jogosultság'], 403);
        }

        $data = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:serviceTags,id',
        ]);

        // Exclusivity check: prevent linking tags that are already used on hotels
        $hotelTagIds = \DB::table('hotelTagRelation')
            ->whereIn('serviceTags_id', $data['tag_ids'])
            ->pluck('serviceTags_id')
            ->unique()
            ->toArray();

        if (!empty($hotelTagIds)) {
            $conflictingTags = ServiceTag::whereIn('id', $hotelTagIds)->pluck('name')->toArray();
            return response()->json([
                'message' => 'These tags are already used on hotels and cannot be linked to rooms: ' . implode(', ', $conflictingTags),
                'conflicting_tags' => $conflictingTags
            ], 422);
        }

        $room->tags()->syncWithoutDetaching($data['tag_ids']);

        return response()->json([
            'message' => 'Tag-ek hozzáadva',
            'tags' => $room->tags
        ]);
    }

    public function destroy(Room $room, ServiceTag $tag)
    {
        if ($room->hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nincs jogosultság'], 403);
        }

        $room->tags()->detach($tag->id);

        return response()->json(['message' => 'Tag eltávolítva']);
    }
}
