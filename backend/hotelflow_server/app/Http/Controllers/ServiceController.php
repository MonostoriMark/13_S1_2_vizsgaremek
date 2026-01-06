<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Hotel;

class ServiceController extends Controller
{
    /**
     * Get all services for a hotel
     */
    public function getServicesByHotelId($hotel_id)
    {
        $hotel = Hotel::find($hotel_id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        $services = Service::where('hotels_id', $hotel_id)->get();
        return response()->json($services, 200);
    }

    /**
     * Get a specific service by ID
     */
    public function getServiceById($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json($service, 200);
    }

    /**
     * Create a new service for a hotel
     */
    public function createService(Request $request)
    {
        $validated = $request->validate([
            'hotels_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0']
        ]);

        // Set price to 0 if not provided (since it's an integer field in DB)
        if (!isset($validated['price']) || $validated['price'] === null) {
            $validated['price'] = 0;
        }

        // Fetch the hotel
        $hotel = Hotel::find($validated['hotels_id']);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $service = Service::create([
            'hotels_id' => $validated['hotels_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null
        ]);

        return response()->json($service, 201);
    }

    /**
     * Update a service
     */
    public function updateService(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Fetch the hotel and check if it belongs to the authenticated user
        $hotel = Hotel::find($service->hotels_id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0']
        ]);

        if (isset($validated['name'])) {
            $service->name = $validated['name'];
        }
        if (isset($validated['description'])) {
            $service->description = $validated['description'];
        }
        if (isset($validated['price'])) {
            $service->price = $validated['price'];
        }

        $service->save();

        return response()->json($service, 200);
    }

    /**
     * Delete a service
     */
    public function deleteService($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Fetch the hotel and check if it belongs to the authenticated user
        $hotel = Hotel::find($service->hotels_id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found'], 404);
        }

        // Check if the hotel belongs to the authenticated user
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Nincs jogosultságod'], 403);
        }

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
}
