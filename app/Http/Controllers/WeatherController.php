<?php

namespace App\Http\Controllers;

use App\Models\Weather;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Display a weather list.
     */
    public function index()
    {
        $weather = Weather::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $weather
        ], 200);
    }

    /**
     * Store a new weather.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $weather = Weather::firstOrCreate([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Weather successfully created',
            'data' => $weather
        ], 200);
    }

    /**
     * Display the specified weather.
     */
    public function show(string $id)
    {
        $weather = Weather::find($id);

        if (empty($weather)) {
            return response()->json([
                'success' => false,
                'message' => 'Weather not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $weather->name,
            'data' => $weather
        ], 200);
    }

    /**
     * Update the specified weather.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $weather = Weather::findOrFail($id);
        $weather->update([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully update weather',
            'data' => $weather
        ], 200);

    }

    /**
     * Remove the specified weather.
     */
    public function destroy(string $id)
    {
        $weather = Weather::find($id);

        if (!$weather) {
            return response()->json([
                'success' => false,
                'message' => 'Weather Not Found'
            ], 404);
        }

        $weather->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weather deleted successfully'
        ], 200);
    }
}
