<?php

namespace App\Http\Controllers;

use App\Http\Resources\PerfumeSuitabilityResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User - Perfume Suitability', 'Endpoint untuk melihat dan mengubah suitability parfum user.', 5)]
class PerfumeSuitabilityController extends Controller
{
    public function show(string $id)
    {
        $perfume = auth()->user()->perfumes()->find($id);

        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        $suitability = $perfume->suitability;

        if (!$suitability) {
            return response()->json([
                'success' => false,
                'message' => 'Suitability data not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Suitability show successfully',
            'data' => new PerfumeSuitabilityResource($suitability),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'ideal_temperature' => 'required|in:dingin,normal,panas',
            'ideal_time' => 'required|in:pagi,siang,malam',
            'ideal_environment' => 'required|in:indoor,outdoor,all around'
        ]);

        $perfume = auth()->user()->perfumes()->find($id);

        if (!$perfume) {
            return response()->json([
                'success' => false,
                'message' => 'Perfume Not Found'
            ], 404);
        }

        $suitability = $perfume->suitability;

        if (!$suitability) {
            return response()->json([
                'success' => false,
                'message' => 'Suitability data not found',
            ], 404);
        }

        $suitability->update($validate);
        return response()->json([
            'success' => true,
            'message' => 'Suitability updated successfully',
            'data' => $suitability,
        ]);


    }
}
