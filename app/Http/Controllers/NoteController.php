<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a notes list.
     */
    public function index()
    {
        $notes = Note::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $notes
        ], 200);
    }

    /**
     * Store a new notes.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $notes = Note::firstOrCreate([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes successfully created',
            'data' => $notes
        ], 200);
    }

    /**
     * Display the specified notes.
     */
    public function show(string $id)
    {
        $notes = Note::find($id);

        if (empty($notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Notes not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $notes->name,
            'data' => $notes
        ], 200);
    }

    /**
     * Update the specified notes.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $notes = Note::findOrFail($id);
        $notes->update([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully update notes',
            'data' => $notes
        ], 200);

    }

    /**
     * Remove the specified notes.
     */
    public function destroy(string $id)
    {
        $notes = Note::find($id);

        if (!$notes) {
            return response()->json([
                'success' => false,
                'message' => 'Notes Not Found'
            ], 404);
        }

        $notes->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notes deleted successfully'
        ], 200);
    }
}
