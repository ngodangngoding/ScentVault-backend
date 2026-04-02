<?php

namespace App\Http\Controllers;

use App\Models\ScentLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScentLogController extends Controller
{
    /**
     * Display a listing of scentlog.
     */
    public function index()
    {
        $scentLogs = ScentLog::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $scentLogs
        ], 200);
    }

    /**
     * Store a newly scentlog.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'perfume_id' => 'required|exists:perfumes,id',
            'user_id' => 'required|exists:users,id',
            'occasion_id' => 'required|exists:occasions,id',
            'environment' => ['required', Rule::in(ScentLog::ENVIRONMENT)],
            'notes_review' => 'nullable|string',
        ]);

        $scentLog = ScentLog::create([
            'perfume_id' => $validate['perfume_id'],
            'user_id' => $validate['user_id'],
            'occasion_id' => $validate['occasion_id'],
            'environment' => $validate['environment'],
            'notes_review' => $validate['notes_review']
        ]);

        return response()->json([
            'success' => true,
            'data' => $scentLog
        ], 200);
    }

    /**
     * Display scentlog.
     */
    public function show(string $id)
    {
        $scentLog = ScentLog::find($id);

        if (empty($scentLog)) {
            return response()->json([
                'success' => false,
                'message' => 'Scentlog not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $scentLog
        ], 200);
    }

    /**
     * Update scentlog.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'perfume_id' => 'required|exists:perfumes,id',
            'user_id' => 'required|exists:users,id',
            'occasion_id' => 'required|exists:occasions,id',
            'environment' => ['required', Rule::in(ScentLog::ENVIRONMENT)],
            'notes_review' => 'nullable|string',
        ]);

        $scentLog = ScentLog::find($id);

        if (empty($scentLog)) {
            return response()->json([
                'success' => false,
                'message' => 'ScentLog not Found'
            ]);
        }

        $scentLog->update([
            'perfume_id' => $validate['perfume_id'],
            'user_id' => $validate['user_id'],
            'occasion_id' => $validate['occasion_id'],
            'environment' => $validate['environment'],
            'notes_review' => $validate['notes_review']
        ]);

        return response()->json([
            'success' => true,
            'data' => $scentLog
        ]);
    }

    /**
     * Remove the scentlog.
     */
    public function destroy(string $id)
    {
        $scentLog = ScentLog::find($id);

        $scentLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfume deleted successfully'
        ], 200);
    }
}
