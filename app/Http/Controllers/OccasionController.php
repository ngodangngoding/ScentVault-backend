<?php

namespace App\Http\Controllers;

use App\Models\Occasion;
use Illuminate\Http\Request;

class OccasionController extends Controller
{
    /**
     * Display a occasions list.
     */
    public function index()
    {
        $occasions = Occasion::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $occasions
        ], 200);
    }

    /**
     * Store a new occasions.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $occasions = Occasion::firstOrCreate([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Occasions successfully created',
            'data' => $occasions
        ], 200);
    }

    /**
     * Display the specified occasions.
     */
    public function show(string $id)
    {
        $occasions = Occasion::find($id);

        if (empty($occasions)) {
            return response()->json([
                'success' => false,
                'message' => 'Occasions not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $occasions->name,
            'data' => $occasions
        ], 200);
    }
    /**
     * Update the specified occasions.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $occasions = Occasion::findOrFail($id);
        $occasions->update([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully update occasions',
            'data' => $occasions
        ], 200);

    }


    /**
     * Remove the specified occasions.
     */
    public function destroy(string $id)
    {
        $occasions = Occasion::find($id);

        if (!$occasions) {
            return response()->json([
                'success' => false,
                'message' => 'Occasions Not Found'
            ], 404);
        }

        $occasions->delete();

        return response()->json([
            'success' => true,
            'message' => 'Occasions deleted successfully'
        ], 200);
    }
}
