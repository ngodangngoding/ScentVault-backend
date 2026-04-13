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
        $scentLogs = ScentLog::with(['perfume', 'occasion'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

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
            'occasion_id' => 'required|exists:occasions,id',
            'weather' => ['required', Rule::in(ScentLog::WEATHER)],
            'notes_review' => 'nullable|string',
        ]);

        $scentLog = ScentLog::create([
            'perfume_id' => $validate['perfume_id'],
            'user_id' => auth()->id(),
            'occasion_id' => $validate['occasion_id'],
            'weather' => $validate['weather'],
            'notes_review' => $validate['notes_review']
        ]);

        return response()->json([
            'success' => true,
            'data' => $scentLog->load(['perfume', 'occasion'])
        ], 201);
    }

    /**
     * Display scentlog.
     */
    public function show(string $id)
    {
        $scentLog = ScentLog::with(['perfume', 'occasion'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (empty($scentLog)) {
            return response()->json([
                'success' => false,
                'message' => 'Scent log not found'
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
            'occasion_id' => 'required|exists:occasions,id',
            'weather' => ['required', Rule::in(ScentLog::WEATHER)],
            'notes_review' => 'nullable|string',
        ]);

        $scentLog = ScentLog::where('user_id', auth()->id())->find($id);

        if (empty($scentLog)) {
            return response()->json([
                'success' => false,
                'message' => 'ScentLog not Found'
            ], 404);
        }

        $scentLog->update([
            'perfume_id' => $validate['perfume_id'],
            'user_id' => auth()->id(),
            'occasion_id' => $validate['occasion_id'],
            'weather' => $validate['weather'],
            'notes_review' => $validate['notes_review']
        ]);

        return response()->json([
            'success' => true,
            'data' => $scentLog->load(['perfume', 'occasion'])
        ], 200);
    }

    /**
     * Remove the scentlog.
     */
    public function destroy(string $id)
    {
        $scentLog = ScentLog::where('user_id', auth()->id())->find($id);

        if (empty($scentLog)) {
            return response()->json([
                'success' => false,
                'message' => 'Scent log not found'
            ], 404);
        }

        $scentLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scent log deleted successfully'
        ], 200);
    }
}
