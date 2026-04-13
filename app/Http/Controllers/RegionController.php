<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Public - Regions', 'Endpoint data wilayah yang bisa dipakai sebelum atau sesudah login.', 2)]
class RegionController extends Controller
{
    public function provinces()
    {
        $data = Region::query()
            ->where('level', 1)
            ->orderBy('name')
            ->get([
                'code',
                'name'
            ]);

        return response()->json($data);
    }

    public function regencies(Request $request)
    {
        $request->validate([
            'province_code' => ['required', 'string', 'exists:region,code'],
        ]);

        $data = Region::query()
            ->where('level', 2)
            ->where('parent_code', $request->province_code)
            ->orderBy('name')
            ->get(
                ['code', 'name']
            );

        return response()->json($data);
    }

    public function districts(Request $request)
    {
        $request->validate([
            'regency_code' => ['required', 'string', 'exists:region,code'],
        ]);

        $data = Region::query()
            ->where('level', 3)
            ->where('parent_code', $request->regency_code)
            ->orderBy('name')
            ->get(
                ['code', 'name']
            );

        return response()->json($data);
    }

    public function villages(Request $request)
    {
        $request->validate([
            'district_code' => ['required', 'string', 'exists:region,code']
        ]);

        $data = Region::query()
            ->where('level', 4)
            ->where('parent_code', $request->district_code)
            ->orderBy('name')
            ->get(
                ['code', 'name']
            );

        return response()->json($data);
    }
}
