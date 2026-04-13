<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerfumeCollectionCategoryResource;
use App\Models\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


#[Group('User - Pages', 'Endpoint data halaman frontend yang membutuhkan login user.', 12)]
class PerfumeCollectionPageController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();

        $categoryId = $validated['category_id'] ?? null;
        $sort = $validated['sort'] ?? 'newest';
        $perPage = $validated['per_page'] ?? 10;

        $perfumeQuery = $user->perfumes()->with([
            'brand',
            'category',
            'perfumeNote',
            'suitability',
        ]);

        if ($categoryId) {
            $perfumeQuery->where('perfumes.category_id', $categoryId);
        }

        if ($sort === 'oldest') {
            $perfumeQuery->orderBy('user_perfumes.created_at', 'asc');
        } else {
            $perfumeQuery->orderBy('user_perfumes.created_at', 'desc');
        }

        $perfumes = $perfumeQuery->paginate($perPage);

        $perfumes->getCollection()->transform(function ($perfume) {
            return [
                'id' => $perfume->id,
                'name' => $perfume->name,
                'brand_name' => $perfume->brand ? $perfume->brand->name : null,
                'category' => [
                    'id' => $perfume->category ? $perfume->category->id : null,
                    'name' => $perfume->category ? $perfume->category->name : null,
                ],
                'concentration' => $perfume->concentration,
                'description' => $perfume->description,
                'image_url' => $perfume->image_url,
                'star_rating' => $perfume->pivot ? $perfume->pivot->star_rating : 0,
                'notes' => $perfume->perfumeNote->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'name' => $note->name,
                        'type' => $note->pivot ? $note->pivot->type : null,
                    ];
                })->values(),
                'suitability' => $perfume->suitability ? [
                    'ideal_temperature' => $perfume->suitability->ideal_temperature,
                    'ideal_time' => $perfume->suitability->ideal_time,
                    'ideal_environment' => $perfume->suitability->ideal_environment,
                ] : null,
                'created_at' => $perfume->pivot ? $perfume->pivot->created_at : null,
                'updated_at' => $perfume->updated_at,
            ];
        });

        $categories = Category::withCount([
            'perfumes as user_perfumes_count' => function ($query) use ($user) {
                $query->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                });
            }
        ])->orderBy('name')->get();

        $categories->each(function ($category) use ($categoryId) {
            $category->is_selected = (int) $categoryId === (int) $category->id;
        });

        return response()->json([
            'success' => true,
            'message' => 'Perfume collection page data fetched successfully',
            'data' => [
                'filters' => [
                    'category_id' => $categoryId,
                    'sort' => $sort,
                    'per_page' => $perPage,
                ],
                'categories' => PerfumeCollectionCategoryResource::collection($categories),
                'perfumes' => [
                    'data' => $perfumes->items(),
                    'pagination' => [
                        'current_page' => $perfumes->currentPage(),
                        'last_page' => $perfumes->lastPage(),
                        'per_page' => $perfumes->perPage(),
                        'total' => $perfumes->total(),
                    ],
                ],
            ],
        ], 200);
    }
}
