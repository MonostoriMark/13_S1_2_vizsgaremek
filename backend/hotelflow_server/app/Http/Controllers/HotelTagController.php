<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\ServiceTag;

class HotelTagController extends Controller
{
    public function store(Request $request, Hotel $hotel)
    {
        // 🔐 jogosultság
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nincs jogosultság'], 403);
        }

        $data = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:serviceTags,id',
        ]);

        // nem duplikál
        $hotel->tags()->syncWithoutDetaching($data['tag_ids']);

        return response()->json([
            'message' => 'Tag-ek hozzáadva',
            'tags' => $hotel->tags
        ]);
    }

    public function destroy(Hotel $hotel, ServiceTag $tag)
    {
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nincs jogosultság'], 403);
        }

        $hotel->tags()->detach($tag->id);

        return response()->json(['message' => 'Tag eltávolítva']);
    }
}
