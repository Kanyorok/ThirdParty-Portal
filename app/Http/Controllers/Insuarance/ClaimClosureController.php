<?php

namespace App\Http\Controllers\Insuarance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimClosureController extends Controller
{
    public function create($id)
    {
        $claim = DB::table('t_BancassuranceClaims')->where('Id', $id)->first();
        $statuses = DB::table('t_CodeDetails')
            ->where('CodeID', 'CLAIM_FINAL_STATUS')
            ->pluck('Description', 'Description')
            ->toArray();

        return view('bancassurance.claims.close', compact('claim', 'statuses'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'FinalStatus' => 'required|string|max:50',
            'FinalRemarks' => 'nullable|string|max:500',
            'ClosureDate' => 'required|date',
        ]);

        DB::table('t_BancassuranceClaimClosures')->insert([
            'ClaimID' => $id,
            'FinalStatus' => $request->FinalStatus,
            'FinalRemarks' => $request->FinalRemarks,
            'ClosureDate' => $request->ClosureDate,
            'ClosedBy' => auth()->id(),
            'CreatedAt' => now(),
        ]);

        DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
            'Status' => $request->FinalStatus,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('bancassurance.claims.index')->with('success', 'Claim successfully closed.');
    }
}
