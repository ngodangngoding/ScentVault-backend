<?php

namespace App\Http\Controllers;

use App\Http\Resources\RuleConfigResource;
use App\Models\RuleConfig;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Admin - Rule Configs', 'Endpoint khusus admin untuk mengatur rule config sistem rekomendasi.', 10)]
class RuleConfigController extends Controller
{
    /**
     * Display a listing of rule configs.
     * Optionally filter by type: ?type=temperature or ?type=time
     */
    public function index(Request $request)
    {
        $query = RuleConfig::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $rules = $query->orderBy('type')->orderBy('min_value')->get();

        return RuleConfigResource::collection($rules);
    }

    /**
     * Store a newly created rule config.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'type' => 'required|in:temperature,time',
            'label' => 'required|string|max:255',
            'min_value' => 'required|numeric',
            'max_value' => 'required|numeric',
        ]);

        $rule = RuleConfig::create($fields);

        return new RuleConfigResource($rule);
    }

    /**
     * Display the specified rule config.
     */
    public function show(RuleConfig $ruleConfig)
    {
        return new RuleConfigResource($ruleConfig);
    }

    /**
     * Update the specified rule config.
     */
    public function update(Request $request, RuleConfig $ruleConfig)
    {
        $fields = $request->validate([
            'type' => 'sometimes|in:temperature,time',
            'label' => 'sometimes|string|max:255',
            'min_value' => 'sometimes|numeric',
            'max_value' => 'sometimes|numeric',
        ]);

        $ruleConfig->update($fields);

        return new RuleConfigResource($ruleConfig);
    }

    /**
     * Remove the specified rule config.
     */
    public function destroy(RuleConfig $ruleConfig)
    {
        $ruleConfig->delete();

        return response()->json([
            'message' => 'Rule config deleted successfully.'
        ]);
    }
}
