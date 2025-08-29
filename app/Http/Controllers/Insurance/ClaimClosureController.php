<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Insurance\InsuranceClosureEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassuranceClaimClosureRequest;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimClosure;
use App\Models\Insurance\BancassuranceClaimPayment;
use App\Services\Insurance\BancassuranceClaimClosureService;
use Illuminate\Support\Carbon;

class ClaimClosureController extends Controller
{
public function closedClaimsIndex()
{
    $closedClaims = BancassuranceClaimClosure::all();
    return view('bancassurance.claims.closed_claims_index', compact('closedClaims'));
}
public function initiateClosureForm()
{
    $claims = BancassuranceClaimPayment::all();
    $status = InsuranceClosureEnum::cases();

    return view('bancassurance.claims.initiate_closure_form', compact('claims','status'));
}

// public function closeForm($id)
// {
//     $claim = DB::table('t_BancassuranceClaims as c')
//         ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
//         ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
//         ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
//         ->select(
//             'c.Id',
//             'c.ClaimType',
//             'c.PolicyID',
//             'p.PolicyNumber',
//             'cu.FullName as CustomerName',
//             'a.ApprovalAmount as ApprovedAmount'
//         )
//         ->where('c.Id', $id)
//         ->first();

//     if (!$claim) {
//         return redirect()->route('bancassurance.claims.index')->with('error', 'Claim not found.');
//     }

//     return view('bancassurance.claims.claim_closure', compact('claim'));
// }
// public function storeClosure(Request $request, $id)
// {
//     $request->validate([
//         'ClosureStatus' => 'required|string|max:100',
//         'ClosureDate' => 'required|date',
//         'Remarks' => 'nullable|string|max:1000'
//     ]);

//     // Insert closure record
//     DB::table('t_BancassuranceClaimClosures')->insert([
//         'ClaimID' => $id,
//         'ClosureStatus' => $request->ClosureStatus,
//         'ClosureDate' => $request->ClosureDate,
//         'Remarks' => $request->Remarks,
//         'ClosedBy' => auth()->id(),
//         'CreatedAt' => now(),
//     ]);

//     // Update the claim status
//     DB::table('t_BancassuranceClaims')
//         ->where('Id', $id)
//         ->update([
//             'Status' => $request->ClosureStatus,
//             'ModifiedBy' => auth()->id(),
//             'ModifiedOn' => now(),
//         ]);

//     return redirect()->route('bancassurance.claims.index')->with('success', 'Claim successfully closed.');
// }

public function storeClosureFromList(BancassuranceClaimClosureRequest $request)
{
    $validated = $request->validated();
    $ClaimId = BancassuranceClaim::findOrFail($validated['ClaimId']);
    $FinalStatus = InsuranceClosureEnum::from($validated['FinalStatus']);

    $closedClaims = BancassuranceClaimClosureService::create(
        $ClaimId,
        $FinalStatus,
        $validated['FinalRemarks'],
        Carbon::parse($validated['ClosureDate']),
        $request->user(),
    );

    return redirect()->route('bancassurance.claims.closed')->with('success', 'Claim successfully closed.');
}

}