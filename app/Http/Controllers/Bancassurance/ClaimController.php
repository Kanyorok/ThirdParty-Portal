<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    //
public function create()
{
    $policies = DB::table('t_BancassurancePolicies')
        ->where('Status', 'Issued') // only issued policies
        ->pluck('PolicyNumber', 'Id');

    return view('bancassurance.claims.create', compact('policies'));
}

public function store(Request $request)
{
    $request->validate([
        'PolicyID' => 'required|exists:t_BancassurancePolicies,Id',
        'ClaimType' => 'required|string|max:100',
        'ClaimReason' => 'required|string|max:255',
        'ClaimAmount' => 'required|numeric|min:0',
        'ClaimDate' => 'required|date',
    ]);

    DB::table('t_BancassuranceClaims')->insert([
        'PolicyID' => $request->PolicyID,
        'ClaimType' => $request->ClaimType,
        'ClaimReason' => $request->ClaimReason,
        'ClaimAmount' => $request->ClaimAmount,
        'ClaimDate' => $request->ClaimDate,
        'Status' => 'Initiated',
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    

    return redirect()->route('bancassurance.claims.index')->with('success', 'Claim initiated successfully.');
}
public function index(Request $request)
{
    $mode = $request->query('mode', 'default');

    $claims = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->select('c.*', 'p.PolicyNumber')
        ->orderByDesc('c.Id')
        ->get();

    return view('bancassurance.claims.index', compact('claims', 'mode'));
}
public function documentUploadForm($claimId)
{
    $claim = DB::table('t_BancassuranceClaims')->where('Id', $claimId)->first();

    $documentTypes = DB::table('t_CodeDetails')
        ->where('CodeID', 'CLAIM_DOCUMENT_TYPE')
        ->pluck('Description', 'Description')
        ->toArray();

    $documents = DB::table('t_BancassuranceClaimDocuments')
        ->where('ClaimID', $claimId)
        ->get();

    return view('bancassurance.claims.documents', compact('claim', 'documents', 'documentTypes'));
}

public function uploadDocuments(Request $request, $claimId)
{
    // Fetch valid document types from CodeDetails
    $validTypes = DB::table('t_CodeDetails')
        ->where('CodeID', 'CLAIM_DOCUMENT_TYPE')
        ->pluck('Description')
        ->toArray();

    // Validation using only allowed values
    $request->validate([
        'DocumentType' => ['required', Rule::in($validTypes)],
        'DocumentName' => 'required|string|max:255',
        'DocumentFile' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);
    
    $documentType = $request->DocumentType === 'Other'
    ? $request->OtherType
    : $request->DocumentType;

    // Store the document in public storage (e.g. storage/app/public/claims)
    $path = $request->file('DocumentFile')->store('claims', 'public');

    // Insert document record
DB::table('t_BancassuranceClaimDocuments')->insert([
    'ClaimID' => $claimId,
    'DocumentType' => $documentType,
    'DocumentName' => $request->DocumentName,
    'FilePath' => $path,
    'UploadedBy' => auth()->id(),
    'UploadedAt' => now(),
]);    
 
    return redirect()->back()->with('success', 'Document uploaded successfully.');
}


public function assessForm($id)
{
    $claim = DB::table('t_BancassuranceClaims')->where('Id', $id)->first();

    if (!$claim) {
        return redirect()->route('bancassurance.claims.index')->with('error', 'Claim not found.');
    }

    return view('bancassurance.claims.assess', compact('claim'));
}

public function storeAssessment(Request $request, $id)
{
    $request->validate([
        'AssessmentComments' => 'required|string|max:1000',
        'Decision' => 'required|in:Approved,Rejected,More Info Needed',
        'AssessedAmount' => 'required|numeric|min:0',
    ]);

    DB::table('t_BancassuranceClaimAssessments')->insert([
        'ClaimID' => $id,
        'AssessmentComments' => $request->AssessmentComments,
        'Decision' => $request->Decision,
        'AssessedAmount' => $request->AssessedAmount,
        'AssessedBy' => auth()->id(),
        'AssessedAt' => now(),
        'AssessmentDate' => now(),
    ]);

    DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
        'Status' => $request->Decision,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now(),
    ]);

    return redirect()->route('bancassurance.claims.index')->with('success', 'Assessment submitted.');
}


public function approvalForm($id)
{
    $claim = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceClaimAssessments as a', 'c.Id', '=', 'a.ClaimID')
        ->select(
            'c.*',
            'p.PolicyNumber',
            'a.AssessedAmount',
            'a.Notes as AssessmentNotes',
            DB::raw("FORMAT(c.ClaimAmount, 'N2') as FormattedClaimAmount"),
            DB::raw("FORMAT(a.AssessedAmount, 'N2') as FormattedAssessedAmount")
        )
        ->where('c.Id', $id)
        ->first();

$documents = DB::table('t_BancassuranceClaimDocuments')
    ->where('ClaimID', $id)
    ->select('FilePath', 'DocumentName')
    ->get();

    if (!$claim) {
        return redirect()->route('bancassurance.claims.approvalQueue')->with('error', 'Claim not found.');
    }

    return view('bancassurance.claims.approve', compact('claim', 'documents'));
}


public function storeApproval(Request $request, $id)
{
    $request->validate([
        'Decision' => 'required|in:Approved,Rejected,More Info Needed',
        'ApprovalAmount' => 'required|numeric|min:0',
        'Remarks' => 'nullable|string|max:500',
        'ApprovalDate' => 'required|date'
    ]);

    DB::table('t_BancassuranceClaimApprovals')->insert([
        'ClaimID' => $id,
        'Decision' => $request->Decision,
        'ApprovalAmount' => $request->ApprovalAmount,
        'Remarks' => $request->Remarks,
        'ApprovalDate' => $request->ApprovalDate,
        'ApprovedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
        'Status' => $request->Decision,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now(),
    ]);

    return redirect()->route('bancassurance.claims.index')->with('success', 'Claim approval recorded successfully.');
}
public function approvalQueue()
{
    $claims = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->select(
            'c.Id', 'c.ClaimType', 'c.ClaimReason', 'c.ClaimAmount', 'c.ClaimDate', 'c.Status',
            'p.PolicyNumber', 'cu.FullName as CustomerName'
        )
        ->where('c.Status', 'Under Assessment')
        ->orderByDesc('c.Id')
        ->get();

    return view('bancassurance.claims.approval_queue', compact('claims'));
}

public function paymentForm($id)
{
    $claim = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
        ->select('c.*', 'p.PolicyNumber', 'a.ApprovalAmount as ApprovedAmount')
        ->where('c.Id', $id)
        ->first();

    return view('bancassurance.claims.claim_payment', compact('claim'));
}

public function storePayment(Request $request, $id)
{
    $request->validate([
        'PaymentDate' => 'required|date',
        'PaymentAmount' => 'required|numeric|min:0',
        'PaymentReference' => 'nullable|string|max:100',
        'Notes' => 'nullable|string|max:500'
    ]);

    DB::table('t_BancassuranceClaimPayments')->insert([
        'ClaimID' => $id,
        'PaymentDate' => $request->PaymentDate,
        'PaymentAmount' => $request->PaymentAmount,
        'PaymentReference' => $request->PaymentReference,
        'Notes' => $request->Notes,
        'PaidBy' => auth()->id(),
        'CreatedAt' => now()
    ]);

    // Update claim status to Paid
    DB::table('t_BancassuranceClaims')->where('Id', $id)->update([
        'Status' => 'Paid',
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.claims.index')->with('success', 'Claim payment recorded.');
}
public function paymentIndex()
{
    $payments = DB::table('t_BancassuranceClaimPayments as pay')
        ->join('t_BancassuranceClaims as c', 'pay.ClaimID', '=', 'c.Id')
        ->join('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
        ->select(
            'pay.*',
            'p.PolicyNumber',
            'cu.FullName as CustomerName',
            'c.ClaimType',
            'a.ApprovalAmount as ApprovedAmount'
        )
        ->orderByDesc('pay.Id')
        ->get();

    return view('bancassurance.claims.claim_payments', compact('payments'));
}
public function closeForm($id)
{
    $claim = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
        ->select(
            'c.Id',
            'c.ClaimType',
            'c.PolicyID',
            'p.PolicyNumber',
            'cu.FullName as CustomerName',
            'a.ApprovalAmount as ApprovedAmount'
        )
        ->where('c.Id', $id)
        ->first();

    if (!$claim) {
        return redirect()->route('bancassurance.claims.index')->with('error', 'Claim not found.');
    }

    return view('bancassurance.claims.claim_closure', compact('claim'));
}
public function storeClosure(Request $request, $id)
{
    $request->validate([
        'ClosureStatus' => 'required|string|max:100',
        'ClosureDate' => 'required|date',
        'Remarks' => 'nullable|string|max:1000'
    ]);

    // Insert closure record
    DB::table('t_BancassuranceClaimClosures')->insert([
        'ClaimID' => $id,
        'ClosureStatus' => $request->ClosureStatus,
        'ClosureDate' => $request->ClosureDate,
        'Remarks' => $request->Remarks,
        'ClosedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    // Update the claim status
    DB::table('t_BancassuranceClaims')
        ->where('Id', $id)
        ->update([
            'Status' => $request->ClosureStatus,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

    return redirect()->route('bancassurance.claims.index')->with('success', 'Claim successfully closed.');
}
public function closedClaimsIndex()
{
    $closedClaims = DB::table('t_BancassuranceClaimClosures as cl')
        ->join('t_BancassuranceClaims as c', 'cl.ClaimID', '=', 'c.Id')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
        ->select(
            'cl.ClosureStatus', 'cl.ClosureDate', 'cl.Remarks',
            'c.Id', 'c.ClaimType',
            'p.PolicyNumber',
            'cu.FullName as CustomerName',
            'a.ApprovalAmount'
        )
        ->orderByDesc('cl.ClosureDate')
        ->get();

    return view('bancassurance.claims.closed_claims_index', compact('closedClaims'));
}
public function initiateClosureForm()
{
    $claims = DB::table('t_BancassuranceClaims as c')
        ->leftJoin('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
        ->leftJoin('t_BancassuranceClaimApprovals as a', 'c.Id', '=', 'a.ClaimID')
        ->select('c.Id', 'c.ClaimType', 'p.PolicyNumber', 'cu.FullName as CustomerName', 'a.ApprovalAmount')
        ->whereIn('c.Status', ['Approved', 'Settled'])
        ->whereNotIn('c.Id', function($query) {
            $query->select('ClaimID')->from('t_BancassuranceClaimClosures');
        })
        ->get();

    return view('bancassurance.claims.initiate_closure_form', compact('claims'));
}

public function storeClosureFromList(Request $request)
{
    $request->validate([
        'ClaimID' => 'required|exists:t_BancassuranceClaims,Id',
        'ClosureStatus' => 'required|string|max:100',
        'Remarks' => 'nullable|string|max:1000',
        'ClosureDate' => 'required|date',
    ]);
DB::table('t_BancassuranceClaimClosures')->insert([
    'ClaimID'       => $request->ClaimID,
    'ClosureStatus' => $request->ClosureStatus,
    'FinalStatus'   => $request->ClosureStatus, // or use another field if desired
    'Remarks'       => $request->Remarks,
    'ClosureDate'   => $request->ClosureDate,
    'ClosedBy'      => auth()->id(),
    'CreatedAt'     => now(),
]);

    DB::table('t_BancassuranceClaims')->where('Id', $request->ClaimID)->update([
        'Status' => 'Closed',
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now(),
    ]);

    return redirect()->route('bancassurance.claims.closed')->with('success', 'Claim successfully closed.');
}

}
