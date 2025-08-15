<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsurancePolicyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassuranceClaimAssessmentRequest;
use App\Http\Requests\Insurance\BancassuranceClaimRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassurancePolicy;
use App\Services\Insurance\BancassuranceClaimAssessmentService;
use App\Services\Insurance\BancassuranceClaimService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClaimController extends Controller
{
    //
public function create()
{
    $this->authorize(PermissionEnum::BancassuranceClaimView, BancassuranceClaim::class);
    $policies = BancassurancePolicy::where('Status', InsurancePolicyStatus::Issued)->get();
    $claimtypes = CodeDetail::where('CodeID', 'ClaimType')->get();
    $claimstatus = CodeDetail::where('CodeID', 'ClaimStatus')->get();
    return view('bancassurance.claims.create', compact('policies','claimtypes','claimstatus'));
}

public function store(BancassuranceClaimRequest $request)
{
    $this->authorize(PermissionEnum::BancassuranceClaimCreate, BancassuranceClaim::class);
    $validated = $request->validated();
    $PolicyId = BancassurancePolicy::findOrFail($validated['PolicyId']);
    $Status = CodeDetail::findOrFail($validated['Status']);
    $ClaimType = CodeDetail::findOrFail($validated['ClaimType']);

    $claim = BancassuranceClaimService::create(
        $PolicyId,
        $ClaimType,
        $validated['ClaimReason'],
        $validated['ClaimAmount'],
        Carbon::parse($validated['ClaimDate']),
        $Status,
        $request->user(),
    );

    return redirect()->route('bancassurance.claims.index')->with('success', 'Claim initiated successfully.');
}

public function index(Request $request)
{
    $mode = $request->query('mode', 'default');

    $claims = BancassuranceClaim::with('claimtype')->get();

    return view('bancassurance.claims.index', compact('claims', 'mode'));
}

public function assessForm($id)
{
    $claim = BancassuranceClaim::find($id);
    $decisions = CodeDetail::where('CodeID', 'Decision')->get();

    if (!$claim) {
        return redirect()->route('bancassurance.claims.index')->with('error', 'Claim not found.');
    }

    return view('bancassurance.claims.assess', compact('claim','decisions'));
}


public function storeAssessment(BancassuranceClaimAssessmentRequest $request, $id)
{
    $validated = $request->validated();

    $claim = BancassuranceClaim::findOrFail($id);
    $Decision = CodeDetail::findOrFail($validated['Decision']);

    $assessment = BancassuranceClaimAssessmentService::create(
        $claim,
        $validated['AssessmentComments'],
        $validated['AssessmentAmount'],
        $Decision,
        $request->user(),
    );

    return redirect()->route('bancassurance.claims.index')->with('success', 'Assessment submitted.');
}


// public function approvalForm($id)
// {
//     $claim = DB::table('t_BancassuranceClaims as c')
//         ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
//         ->leftJoin('t_BancassuranceClaimAssessments as a', 'c.Id', '=', 'a.ClaimID')
//         ->select(
//             'c.*',
//             'p.PolicyNumber',
//             'a.AssessedAmount',
//             'a.Notes as AssessmentNotes',
//             DB::raw("FORMAT(c.ClaimAmount, 'N2') as FormattedClaimAmount"),
//             DB::raw("FORMAT(a.AssessedAmount, 'N2') as FormattedAssessedAmount")
//         )
//         ->where('c.Id', $id)
//         ->first();

// $documents = DB::table('t_BancassuranceClaimDocuments')
//     ->where('ClaimID', $id)
//     ->select('FilePath', 'DocumentName')
//     ->get();

//     if (!$claim) {
//         return redirect()->route('bancassurance.claims.approvalQueue')->with('error', 'Claim not found.');
//     }

//     return view('bancassurance.claims.approve', compact('claim', 'documents'));
// }


// public function storeApproval(Request $request, $id)
// {
//     $request->validate([
//         'Decision' => 'required|in:Approved,Rejected,More Info Needed',
//         'ApprovalAmount' => 'required|numeric|min:0',
//         'Remarks' => 'nullable|string|max:500',
//         'ApprovalDate' => 'required|date'
//     ]);

//     DB::table('t_BancassuranceClaimApprovals')->insert([
//         'ClaimID' => $id,
//         'Decision' => $request->Decision,
//         'ApprovalAmount' => $request->ApprovalAmount,
//         'Remarks' => $request->Remarks,
//         'ApprovalDate' => $request->ApprovalDate,
//         'ApprovedBy' => auth()->id(),
//         'CreatedAt' => now(),
//     ]);

//     DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
//         'Status' => $request->Decision,
//         'ModifiedBy' => auth()->id(),
//         'ModifiedOn' => now(),
//     ]);

//     return redirect()->route('bancassurance.claims.index')->with('success', 'Claim approval recorded successfully.');
// }
// public function approvalQueue()
// {
//     $claims = DB::table('t_BancassuranceClaims as c')
//         ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
//         ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
//         ->select(
//             'c.Id', 'c.ClaimType', 'c.ClaimReason', 'c.ClaimAmount', 'c.ClaimDate', 'c.Status',
//             'p.PolicyNumber', 'cu.FullName as CustomerName'
//         )
//         ->where('c.Status', 'Under Assessment')
//         ->orderByDesc('c.Id')
//         ->get();

//     return view('bancassurance.claims.approval_queue', compact('claims'));
// }

// public function paymentForm($id)
// {
//     $claim = BancassuranceClaim::findOrFail($id);

//     return view('bancassurance.claims.claim_payment', compact('claim'));
// }

// public function storePayment(Request $request, $id)
// {
//     $request->validate([
//         'PaymentDate' => 'required|date',
//         'PaymentAmount' => 'required|numeric|min:0',
//         'PaymentReference' => 'nullable|string|max:100',
//         'Notes' => 'nullable|string|max:500'
//     ]);

//     DB::table('t_BancassuranceClaimPayments')->insert([
//         'ClaimID' => $id,
//         'PaymentDate' => $request->PaymentDate,
//         'PaymentAmount' => $request->PaymentAmount,
//         'PaymentReference' => $request->PaymentReference,
//         'Notes' => $request->Notes,
//         'PaidBy' => auth()->id(),
//         'CreatedAt' => now()
//     ]);

//     // Update claim status to Paid
//     DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
//         'Status' => 'Paid',
//         'ModifiedBy' => auth()->id(),
//         'ModifiedOn' => now()
//     ]);

//     return redirect()->route('bancassurance.claims.index')->with('success', 'Claim payment recorded.');
// }
// public function paymentIndex()
// {
//     $payments = BancassuranceClaimAssessment::all();

//     return view('bancassurance.claims.claim_payments', compact('payments'));
// }
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
public function closedClaimsIndex()
{
    $closedClaims = DB::table('t_BancassuranceClaimClosures as cl')
        ->join('t_BancassuranceClaims as c', 'cl.ClaimID', '=', 'c.Id')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->orderByDesc('cl.ClosureDate')
        ->get();

    return view('bancassurance.claims.closed_claims_index', compact('closedClaims'));
}
public function initiateClosureForm()
{
    $claims = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->whereIn('c.Status', ['Approved', 'Settled'])
        ->whereNotIn('c.Id', function($query) {
            $query->select('ClaimID')->from('t_BancassuranceClaimClosures');
        })
        ->get();

    return view('bancassurance.claims.initiate_closure_form', compact('claims'));
}

// public function storeClosureFromList(Request $request)
// {
//     $request->validate([
//         'ClaimID' => 'required|exists:t_BancassuranceClaims,Id',
//         'ClosureStatus' => 'required|string|max:100',
//         'Remarks' => 'nullable|string|max:1000',
//         'ClosureDate' => 'required|date',
//     ]);
// DB::table('t_BancassuranceClaimClosures')->insert([
//     'ClaimID'       => $request->ClaimID,
//     'ClosureStatus' => $request->ClosureStatus,
//     'FinalStatus'   => $request->ClosureStatus, // or use another field if desired
//     'Remarks'       => $request->Remarks,
//     'ClosureDate'   => $request->ClosureDate,
//     'ClosedBy'      => auth()->id(),
//     'CreatedAt'     => now(),
// ]);

//     DB::table('t_BancassuranceClaims')->where('Id', $request->ClaimID)->update([
//         'Status' => 'Closed',
//         'ModifiedBy' => auth()->id(),
//         'ModifiedOn' => now(),
//     ]);

//     return redirect()->route('bancassurance.claims.closed')->with('success', 'Claim successfully closed.');
// }

}
