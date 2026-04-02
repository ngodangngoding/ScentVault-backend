<?php

namespace App\Http\Controllers;

use App\Http\Resources\PerfumeResource;
use App\Models\Perfume;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PerfumeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perfume = Perfume::latest()->get();
        return response()->json([
            'success' => true,
            'data' => PerfumeResource::collection($perfume)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|max:255',
            'concentration' => ['required', Rule::in(Perfume::CONCENTRATION)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_active' => 'boolean|nullable',
            'star_rating' => 'integer|nullable',
            'brand_id' => 'required|exists:brands,id',
            'notes' => 'required|array',
            'notes.*.note_id' => 'required|exists:notes,id',
            'notes.*.type' => 'required|in:top,middle,base',

        ]);

        $photoPath = '';
        if ($request->hasFile('image')) {
            $photo = $request->file('image');
            $path = $photo->store('perfumes', 'public');
            $photoPath = $path;
        }


        DB::beginTransaction();

        try {
            $perfume = Perfume::create([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'is_active' => $validate['is_active'],
                'star_rating' => $validate['star_rating'],
                'brand_id' => $validate['brand_id']
            ]);

            $notesData = [];
            foreach ($validate['notes'] as $note) {
                $notesData[$note['note_id']] = ['type' => $note['type']];
            }

            $perfume->perfumeNote()->sync($notesData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully created',
                'data' => new PerfumeResource($perfume->load('perfumeNote'))
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Perfume',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $perfume = Perfume::with('brand')->find($id);
        if (empty($perfume)) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $perfume->name,
            'data' => new PerfumeResource($perfume->load('perfumeNote'))
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|max:255',
            'concentration' => ['required', Rule::in(Perfume::CONCENTRATION)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_active' => 'boolean|nullable',
            'star_rating' => 'integer|nullable',
            'brand_id' => 'required|exists:brands,id',
            'notes' => 'required|array',
            'notes.*.note_id' => 'required|exists:notes,id',
            'notes.*.type' => 'required|in:top,middle,base',
        ]);

        $perfume = Perfume::findOrFail($id);
        $photoPath = $perfume->image;

        if ($request->hasFile('image')) {
            if ($perfume->image && Storage::disk('public')->exists($perfume->image)) {
                Storage::disk('public')->delete($perfume->image);
            }
            $photo = $request->file('image');
            $photoPath = $photo->store('perfumes', 'public');
        }

        DB::beginTransaction();

        try {
            $perfume->update([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'is_active' => $validate['is_active'],
                'star_rating' => $validate['star_rating'],
                'brand_id' => $validate['brand_id']
            ]);

            $notesData = [];
            foreach ($validate['notes'] as $note) {
                $notesData[$note['note_id']] = ['type' => $note['type']];
            }
            $perfume->perfumeNote()->sync($notesData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully updated',
                'data' => new PerfumeResource($perfume->load('perfumeNote'))
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Perfume',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $perfume = Perfume::find($id);

        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        $perfume->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfume deleted successfully'
        ], 200);
    }
}
