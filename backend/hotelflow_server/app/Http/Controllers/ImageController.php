<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    // 1️⃣ Kép feltöltése + kapcsolás szobához/ szobákhoz
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096', // 4MB
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'exists:rooms,id'
        ]);

        // fájl mentése
        $path = $request->file('image')->store('room_images', 'public');

        // új image rekord létrehozása
        $image = Image::create([
            'url' => Storage::url($path)
        ]);

        // kapcsolatok mentése
        $image->rooms()->sync($request->rooms);

        return response()->json([
            'message' => 'Kép feltöltve és kapcsolva',
            'image' => $image
        ], 201);
    }

    // 2️⃣ Létező kép hozzárendelése szobákhoz
    public function link(Request $request)
    {
        $request->validate([
            'imageId' => 'required|exists:images,id',
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'exists:rooms,id'
        ]);

        $image = Image::find($request->imageId);
        $image->rooms()->syncWithoutDetaching($request->rooms);

        return response()->json(['message' => 'Kapcsolat létrehozva']);
    }

    // 3️⃣ Kapcsolat eltávolítása (nem törli a képet)
    public function unlink(Request $request)
    {
        $request->validate([
            'imageId' => 'required|exists:images,id',
            'roomId' => 'required|exists:rooms,id'
        ]);

        $image = Image::find($request->imageId);
        $image->rooms()->detach($request->roomId);

        return response()->json(['message' => 'Kapcsolat törölve']);
    }

    // 4️⃣ Kép törlése (file + relations + db)
    public function destroy($id)
    {
        $image = Image::findOrFail($id);

        // Storage-ből törlés
        $filePath = str_replace('/storage/', '', $image->url);
        Storage::disk('public')->delete($filePath);

        // DB törlés
        $image->delete();

        return response()->json(['message' => 'Kép törölve']);
    }

    // 5️⃣ Szoba képei
    public function roomImages($roomId)
    {
        $room = Room::with('images')->findOrFail($roomId);

        return response()->json($room->images);
    }

    // 6️⃣ Egy kép adatai + mely szobákhoz tartozik
    public function show($id)
    {
        $image = Image::with('rooms')->findOrFail($id);
        return response()->json($image);
    }
}
