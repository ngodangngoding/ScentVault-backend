<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserPerfumeResource;
use App\Models\Perfume;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;



class PerfumeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = auth()->user();
        $perfumes = $user->perfumes()->with(['brand', 'perfumeNote', 'suitability'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => UserPerfumeResource::collection($perfumes)
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
            'brand_id' => 'required|exists:brands,id',
            'notes' => 'required|array',
            'notes.*.note_id' => 'required|exists:notes,id',
            'notes.*.type' => 'required|in:top,middle,base',
            'is_active' => 'boolean|nullable',
            'star_rating' => 'integer|nullable',
        ]);

        $photoPath = '';
        if ($request->hasFile('image')) {
            $photo = $request->file('image');
            $photoPath = $photo->store('perfumes', 'public');
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

            // Pivot Perfumes <-> Notes
            $notesData = [];
            foreach ($validate['notes'] as $note) {
                $notesData[$note['note_id']] = ['type' => $note['type']];
            }
            $perfume->perfumeNote()->sync($notesData);

            // Pivot User <-> Perfume
            auth()->user()->perfumes()->attach($perfume->id, [
                'is_active' => $validate['is_active'] ?? false,
                'star_rating' => $validate['star_rating'] ?? 0
            ]);

            $perfume->suitability()->create([
                'ideal_temperature' => 'normal',
                'ideal_time' => 'siang',
                'ideal_environment' => 'indoor'
            ]);

            DB::commit();

            $perfumeWithPivot = auth()->user()->perfumes()->with(['brand', 'perfumeNote', 'suitability'])->find($perfume->id);

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully created',
                'data' => new UserPerfumeResource($perfumeWithPivot)
            ], 201);
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
        $perfume = auth()->user()->perfumes()->with(['brand', 'perfumeNote', 'suitability'])->find($id);

        if (empty($perfume)) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $perfume->name,
            'data' => new UserPerfumeResource($perfume)
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

        $perfume = auth()->user()->perfumes()->find($id);
        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        $photoPath = $perfume->image;

        if ($request->hasFile('image')) {
            if ($perfume->image && Storage::disk('public')->exists($perfume->image)) {
                Storage::disk('public')->delete($perfume->image);
            }
            $photoPath = $request->file('image')->store('perfumes', 'public');
        }

        DB::beginTransaction();

        try {
            $perfume->update([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'brand_id' => $validate['brand_id']
            ]);

            // Pivot: Perfume <-> Notes
            $notesData = [];
            foreach ($validate['notes'] as $note) {
                $notesData[$note['note_id']] = ['type' => $note['type']];
            }
            $perfume->perfumeNote()->sync($notesData);

            // Pivot: User <-> Perfume
            auth()->user()->perfumes()->updateExistingPivot($perfume->id, [
                'is_active' => $validate['is_active'] ?? false,
                'star_rating' => $validate['star_rating'] ?? 0
            ]);

            DB::commit();

            $perfumeWithPivot = auth()->user()->perfumes()->with(['brand', 'perfumeNote', 'suitability'])->find($perfume->id);

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully updated',
                'data' => new userPerfumeResource($perfumeWithPivot)
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
        $perfume = auth()->user()->perfumes()->find($id);

        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        if ($perfume->image) {
            Storage::disk('public')->delete($perfume->image);
        }

        auth()->user()->perfumes()->detach($perfume->id);
        $perfume->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfume deleted successfully'
        ], 200);
    }
}
