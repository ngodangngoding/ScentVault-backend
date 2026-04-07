<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a category list.
     */
    public function index()
    {
        $categories = Category::withCount('perfumes')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::firstOrCreate([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category successfully created',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(string $id)
    {
        $category = Category::withCount('perfumes')->find($id);

        if (empty($category)) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail of ' . $category->name,
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $validate['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated category',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
