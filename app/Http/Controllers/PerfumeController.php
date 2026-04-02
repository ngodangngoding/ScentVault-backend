<?php

namespace App\Http\Controllers;

use App\Models\Perfume;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
            'data' => $perfume
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
            'brand_id' => 'required|exists:brands,id'
        ]);

        $photoPath = '';

        if ($request->hasFile('image')) {
            $photo = $request->file('image');
            $path = $photo->store('perfumes', 'public');
            $photoPath = $path;
        }

        $perfume = Perfume::create([
            'name' => $validate['name'],
            'concentration' => $validate['concentration'],
            'description' => $validate['description'],
            'image' => $photoPath,
            'is_active' => $validate['is_active'],
            'star_rating' => $validate['star_rating'],
            'brand_id' => $validate['brand_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfume successfully created',
            'data' => $perfume
        ], 200);
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
            'data' => $perfume
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
            'brand_id' => 'required|exists:brands,id'
        ]);

        $photoPath = '';

        if ($request->hasFile('image')) {
            $photo = $request->file('image');
            $path = $photo->store('perfumes', 'public');
            $photoPath = $path;
        }

        try {
            $perfume = Perfume::findOrFail($id);
            $perfume->update([
                'name' => $validate['name'],
                'concentration' => $validate['concentration'],
                'description' => $validate['description'],
                'image' => $photoPath,
                'is_active' => $validate['is_active'],
                'star_rating' => $validate['star_rating'],
                'brand_id' => $validate['brand_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfume successfully created',
                'data' => $perfume
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume not found'
            ], 404);
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
