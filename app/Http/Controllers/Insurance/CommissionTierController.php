<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionTierController extends Controller
{
    public function index($ruleId)
    {
        // Return tiers for a specific commission rule
        $tiers = DB::table('t_CommissionTiers')
            ->where('CommissionRuleID', $ruleId)
            ->orderBy('MinAmount')
            ->get();

        return response()->json($tiers);
    }

    public function store(Request $request, $ruleId)
    {
        $validated = $request->validate([
            'MinAmount' => 'required|numeric|min:0',
            'MaxAmount' => 'nullable|numeric|min:0',
            'CommissionRate' => 'required|numeric|min:0|max:100',
            'Description' => 'nullable|string',
        ]);

        try {
            DB::table('t_CommissionTiers')->insert([
                'CommissionRuleID' => $ruleId,
                'MinAmount' => $validated['MinAmount'],
                'MaxAmount' => $validated['MaxAmount'] ?? null,
                'CommissionRate' => $validated['CommissionRate'],
                'Description' => $validated['Description'] ?? null,
                'CreatedBy' => Auth::id() ?? 1,
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id() ?? 1,
                'ModifiedOn' => now(),
            ]);

            return response()->json(['message' => 'Commission tier created successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create commission tier: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create commission tier'], 500);
        }
    }
}
