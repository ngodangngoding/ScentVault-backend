<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserPerfumeResource;
use App\Models\Brand;
use App\Models\Note;
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
        $perfumes = $user->perfumes()->with(['brand', 'category', 'perfumeNote', 'suitability'])->latest()->get();

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
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'notes' => 'required|array',
            'notes.*.name' => 'required|string|max:255',
            'notes.*.type' => 'required|in:top,middle,base',
            'star_rating' => 'integer|nullable',
        ]);

        $photoPath = '';
        if ($request->hasFile('image')) {
            $photo = $request->file('image');
            $photoPath = $photo->store('perfumes', 'public');
        }


        DB::beginTransaction();

        try {
            $brandName = trim(ucwords(strtolower($validate['brand'])));
            $brand = Brand::firstOrCreate(['name' => $brandName]);

            $perfume = Perfume::create([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'star_rating' => $validate['star_rating'],
                'brand_id' => $brand->id,
                'category_id' => $validate['category_id']
            ]);

            // Pivot Perfumes <-> Notes (auto-create note jika belum ada)
            $notesData = [];
            foreach ($validate['notes'] as $noteInput) {
                $note = Note::firstOrCreate(
                    ['name' => strtolower(trim($noteInput['name']))]
                );
                $notesData[$note->id] = ['type' => $noteInput['type']];
            }
            $perfume->perfumeNote()->sync($notesData);

            // Pivot User <-> Perfume
            auth()->user()->perfumes()->attach($perfume->id, [
                'star_rating' => $validate['star_rating'] ?? 0
            ]);

            $perfume->suitability()->create([
                'ideal_temperature' => 'normal',
                'ideal_time' => 'siang',
                'ideal_environment' => 'indoor'
            ]);

            DB::commit();

            $perfumeWithPivot = auth()->user()->perfumes()->with(['brand', 'category', 'perfumeNote', 'suitability'])->find($perfume->id);

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
        $perfume = auth()->user()->perfumes()->with(['brand', 'category', 'perfumeNote', 'suitability'])->find($id);

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
            'star_rating' => 'integer|nullable',
            'brand' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'notes' => 'required|array',
            'notes.*.name' => 'required|string|max:255',
            'notes.*.type' => 'required|in:top,middle,base',
        ]);

        $perfume = auth()->user()->perfumes()->find($id);
        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        $oldBrandId = $perfume->brand_id;
        $oldNoteIds = $perfume->perfumeNote->pluck('id')->toArray();

        $photoPath = $perfume->image;

        if ($request->hasFile('image')) {
            if ($perfume->image && Storage::disk('public')->exists($perfume->image)) {
                Storage::disk('public')->delete($perfume->image);
            }
            $photoPath = $request->file('image')->store('perfumes', 'public');
        }

        DB::beginTransaction();

        try {
            $brandName = trim(ucwords(strtolower($validate['brand'])));
            $brand = Brand::firstOrCreate(['name' => $brandName]);

            $perfume->update([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'brand_id' => $brand->id,
                'category_id' => $validate['category_id']
            ]);

            // Pivot: Perfume <-> Notes (auto-create note jika belum ada)
            $notesData = [];
            foreach ($validate['notes'] as $noteInput) {
                $note = Note::firstOrCreate(
                    ['name' => strtolower(trim($noteInput['name']))]
                );
                $notesData[$note->id] = ['type' => $noteInput['type']];
            }
            $perfume->perfumeNote()->sync($notesData);

            // Pivot: User <-> Perfume
            auth()->user()->perfumes()->updateExistingPivot($perfume->id, [
                'star_rating' => $validate['star_rating'] ?? 0
            ]);

            // Cleanup orphaned old brand if no longer used
            if ($oldBrandId != $brand->id) {
                if (Brand::where('id', $oldBrandId)->doesntHave('perfumes')->exists()) {
                    Brand::destroy($oldBrandId);
                }
            }

            // Cleanup orphaned old notes
            $newNoteIds = array_keys($notesData);
            $potentiallyOrphanedNotes = array_diff($oldNoteIds, $newNoteIds);
            if (!empty($potentiallyOrphanedNotes)) {
                $trulyOrphaned = Note::whereIn('id', $potentiallyOrphanedNotes)
                    ->doesntHave('perfumeNotes')
                    ->pluck('id')
                    ->toArray();
                if (!empty($trulyOrphaned)) {
                    Note::destroy($trulyOrphaned);
                }
            }

            DB::commit();

            $perfumeWithPivot = auth()->user()->perfumes()->with(['brand', 'category', 'perfumeNote', 'suitability'])->find($perfume->id);

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully updated',
                'data' => new UserPerfumeResource($perfumeWithPivot)
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

        $brandId = $perfume->brand_id;
        $noteIds = $perfume->perfumeNote->pluck('id')->toArray();

        if ($perfume->image) {
            Storage::disk('public')->delete($perfume->image);
        }

        DB::beginTransaction();

        try {
            auth()->user()->perfumes()->detach($perfume->id);
            $perfume->perfumeNote()->detach();
            if ($perfume->suitability) {
                $perfume->suitability()->delete();
            }
            $perfume->delete();

            if (Brand::where('id', $brandId)->doesntHave('perfumes')->exists()) {
                Brand::destroy($brandId);
            }

            if (!empty($noteIds)) {
                $trulyOrphaned = Note::whereIn('id', $noteIds)
                    ->doesntHave('perfumeNotes')
                    ->pluck('id')
                    ->toArray();
                if (!empty($trulyOrphaned)) {
                    Note::destroy($trulyOrphaned);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfume deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Perfume',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
