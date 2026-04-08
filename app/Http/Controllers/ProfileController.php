<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateRegion(Request $request)
    {
        $validated = $request->validate([
            'region_code' => [
                'required',
                'string',
                Rule::exists('region', 'code')->where(
                    fn ($query) => $query->where('level', 4)
                ),
            ],
        ]);

        $user = $request->user();
        $user->region_code = $validated['region_code'];
        $user->save();

        return response()->json([
            'message' => 'Region berhasil disimpan',
            'user' => $user,
        ]);
    }
}
