<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brand = Brand::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $brand
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $brand = Brand::firstOrCreate([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand successfully created',
            'data' => $brand
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            return response()->json([
                'success' => false,
                'message' => 'Brands not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $brand->name,
            'data' => $brand
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $brand = Brand::findOrFail($id);
        $brand->update([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully update brand',
            'data' => $brand
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand Not Found'
            ], 404);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ], 200);
    }
}
