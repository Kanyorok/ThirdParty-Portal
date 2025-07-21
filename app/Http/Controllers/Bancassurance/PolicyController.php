<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PolicyController extends Controller
{
    //
public function create(Request $request)
{
    $referral = null;
    $prefilled = [];

    // 1. Auto-prefill from referral_id in URL (if present)
    if ($request->filled('referral_id')) {
        $referral = DB::table('t_BancassuranceReferrals')->where('Id', $request->referral_id)->first();

        if ($referral) {
            $prefilled = [
                'CustomerID' => $referral->CustomerID,
                'ProductID' => $referral->ProductID,
                'InsurerID' => $referral->PreferredInsurerID
            ];
        }
    }

    // 2. Load data for form dropdowns
    $customers = DB::table('t_BancassuranceCustomers')->get();
    $products = DB::table('t_InsuranceProducts')->get();
    $insurers = DB::table('t_InsuranceProviders')->get();

    // 3. OPTIONAL: Allow manual selection of referrals
    $referrals = DB::table('t_BancassuranceReferrals')
        ->where('Status', '!=', 'Converted')
        ->get();

    return view('bancassurance.policies.create', compact(
        'customers', 'products', 'insurers', 'referral', 'prefilled', 'referrals'
    ));
}


public function store(Request $request)
{
    $request->validate([
        'CustomerID' => 'required',
        'ProductID' => 'required',
        'SumAssured' => 'required|numeric',
        'PremiumAmount' => 'required|numeric',
        'PaymentFrequency' => 'required',
        'PolicyStartDate' => 'required|date',
        'PolicyEndDate' => 'required|date',
    ]);

    DB::table('t_BancassurancePolicies')->insert([
        'ReferralID' => $request->ReferralID, // new field
        'CustomerID' => $request->CustomerID,
        'ProductID' => $request->ProductID,
        'InsurerID' => $request->InsurerID,
        'PolicyNumber' => $request->PolicyNumber,
        'SumAssured' => $request->SumAssured,
        'PremiumAmount' => $request->PremiumAmount,
        'PaymentFrequency' => $request->PaymentFrequency,
        'PolicyStartDate' => $request->PolicyStartDate,
        'PolicyEndDate' => $request->PolicyEndDate,
        'Status' => 'Proposal',
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    // Optional: Update referral status
    if ($request->filled('ReferralID')) {
        DB::table('t_BancassuranceReferrals')->where('Id', $request->ReferralID)->update([
            'Status' => 'Converted',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    }

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy proposal submitted.');
}
public function index(Request $request)
{
    $query = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as pr', 'p.ProductID', '=', 'pr.Id')
        ->leftJoin('t_InsuranceProviders as i', 'p.InsurerID', '=', 'i.Id')
        ->select(
            'p.*',
            DB::raw("CONCAT(c.FullName, ' (', c.NationalID, ')') as CustomerName"),
            'pr.Name as ProductName',
            'i.Name as InsurerName'
        );

    // ✅ Apply filters
    if ($request->filled('status')) {
        $query->where('p.Status', $request->status);
    }

    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('p.PolicyStartDate', [$request->from, $request->to]);
    }

    if ($request->filled('customer')) {
        $query->where('c.FullName', 'like', '%' . $request->customer . '%');
    }

    $policies = $query->orderByDesc('p.Id')->get();

    return view('bancassurance.policies.index', compact('policies'));
}

public function submitForUnderwriting(Request $request, $id)
{
    $request->validate([
        'documents.*' => 'required|file|max:2048',
        'underwriter_email' => 'required|email'
    ]);

    $policy = DB::table('t_BancassurancePolicies')->where('Id', $id)->first();

    if (!$policy) {
        return redirect()->back()->with('error', 'Policy not found.');
    }

    // Upload files and save paths
    $uploadedPaths = [];
    foreach ($request->file('documents') as $file) {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("bancassurance/proposals/{$id}", $fileName, 'public');

        DB::table('t_BancassuranceProposalDocuments')->insert([
            'PolicyID' => $id,
            'FilePath' => $path,
            'FileName' => $fileName,
            'UploadedBy' => auth()->id(),
            'UploadedAt' => now()
        ]);

        $uploadedPaths[] = storage_path("app/public/{$path}");
    }

    // Send email to underwriter
    Mail::raw("Dear Underwriter,\n\nPlease review the attached proposal for Customer ID: {$policy->CustomerID}\n\nRegards,\nBancassurance Team", function ($message) use ($request, $uploadedPaths) {
        $message->to($request->underwriter_email)
            ->subject('New Proposal for Underwriting');

        foreach ($uploadedPaths as $file) {
            $message->attach($file);
        }
    });

    // Update policy status
    DB::table('t_BancassurancePolicies')->where('Id', $id)->update([
        'Status' => 'SubmittedForUnderwriting',
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.policies.index')->with('success', 'Proposal submitted to underwriter.');
}


public function reviewIndex()
{
    $proposals = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->select(
            'p.Id',
            'p.PolicyNumber',
            'c.FullName as CustomerName',
            'prod.Name as ProductName',
            'p.SumAssured',
            'p.Status',
            'p.CreatedAt'
        )
        ->whereIn('p.Status', ['Proposal', 'SubmittedForUnderwriting'])
        ->orderByDesc('p.Id')
        ->get();

    return view('bancassurance.policies.review_index', compact('proposals'));
}
public function review($id)
{
    $policy = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->select(
            'p.*',
            'c.FullName as CustomerName',
            'prod.Name as ProductName'
        )
        ->where('p.Id', $id)
        ->first();

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
    }

    return view('bancassurance.policies.review', compact('policy'));
}

// Show the feedback form
public function feedbackForm($id)
{
    $policy = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->leftJoin('t_InsuranceProviders as ip', 'p.InsurerID', '=', 'ip.Id')
        ->select(
            'p.Id',
            'p.PolicyNumber',
            'p.Status',
            'c.FullName as CustomerName',
            'prod.Name as ProductName',
            'ip.Name as InsurerName'
        )
        ->where('p.Id', $id)
        ->first();

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
    }

    return view('bancassurance.policies.feedback', compact('policy'));
}

public function storeFeedback(Request $request, $id)
{
    $request->validate([
        'FeedbackDate'      => 'required|date',
        'UnderwriterName'   => 'required|string|max:100',
        'Comments'          => 'nullable|string|max:500',
        'Decision'          => 'required|in:Approved,Declined,More Info Needed',
        'RiskScore'         => 'nullable|numeric|min:0|max:100'
    ]);

    // Check if policy exists before inserting
    $policyExists = DB::table('t_BancassurancePolicies')->where('Id', $id)->exists();
    if (!$policyExists) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found for feedback.');
    }

    // Insert feedback record
    DB::table('t_BancassuranceUnderwritingFeedback')->insert([
        'PolicyID'        => $id,
        'FeedbackDate'    => $request->FeedbackDate,
        'UnderwriterName' => $request->UnderwriterName,
        'Comments'        => $request->Comments,
        'Decision'        => $request->Decision,
        'RiskScore'       => $request->RiskScore,
        'CreatedBy'       => auth()->id(),
        'CreatedAt'       => now(),
    ]);

    // Update policy status based on decision
    $newStatus = match ($request->Decision) {
        'Approved' => 'ApprovedForIssuance',
        'Declined' => 'Declined',
        default    => 'AwaitingClarification'
    };

    DB::table('t_BancassurancePolicies')->where('Id', $id)->update([
        'Status'     => $newStatus,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.policies.index')->with('success', 'Underwriting feedback recorded successfully.');
}
public function feedbackList()
{
    $proposals = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->leftJoin('t_InsuranceProviders as ip', 'p.InsurerID', '=', 'ip.Id')
        ->select(
            'p.Id',
            'p.PolicyNumber',
            'p.Status',
            'c.FullName as CustomerName',
            'prod.Name as ProductName',
            'ip.Name as InsurerName',
            'p.CreatedAt'
        )
        ->where('p.Status', 'SubmittedForUnderwriting') // Adjust status if needed
        ->orderByDesc('p.Id')
        ->get();

    return view('bancassurance.policies.feedback-list', compact('proposals'));
}
// Show issuance form
public function issueForm($id)
{
    $policy = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->select('p.*', 'c.FullName as CustomerName')
        ->where('p.Id', $id)
        ->first();

    if (!$policy || $policy->Status !== 'ApprovedForIssuance') {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not eligible for issuance.');
    }

    return view('bancassurance.policies.issue', compact('policy'));
}

// Store issuance details
public function storeIssuance(Request $request, $id)
{
    $request->validate([
        'IssuedDate' => 'required|date',
        'ExpiryDate' => 'required|date|after:IssuedDate',
        'PolicyNumber' => 'required|string|max:50',
        'PolicyDocument' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
    ]);

    $filePath = null;
    if ($request->hasFile('PolicyDocument')) {
        $filePath = $request->file('PolicyDocument')->store('policies', 'public');
    }

    DB::table('t_BancassurancePolicies')->where('Id', $id)->update([
        'PolicyNumber' => $request->PolicyNumber,
        'IssuedDate' => $request->IssuedDate,
        'ExpiryDate' => $request->ExpiryDate,
        'PolicyDocumentPath' => $filePath,
        'IsIssued' => 1,
        'Status' => 'Issued',
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy issued successfully.');
}
public function issuanceList()
{
    $policies = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProviders as ip', 'p.InsurerID', '=', 'ip.Id')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->where('p.Status', 'ApprovedForIssuance')
        ->select(
            'p.Id',
            'p.PolicyNumber',
            'c.FullName as CustomerName',
            'prod.Name as ProductName',
            'ip.Name as InsurerName',
            'p.Status',
            'p.CreatedAt'
        )
        ->orderByDesc('p.Id')
        ->get();

    return view('bancassurance.policies.issuance-list', compact('policies'));
}
public function endorsementForm($id)
{
    $policy = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->select('p.Id', 'p.PolicyNumber', 'c.FullName as CustomerName')
        ->where('p.Id', $id)
        ->first();

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
    }

    return view('bancassurance.policies.endorsement', compact('policy'));
}

public function storeEndorsement(Request $request, $id)
{
    $request->validate([
        'EndorsementType' => 'required|string|max:100',
        'RequestDate' => 'required|date',
        'EffectiveDate' => 'required|date|after_or_equal:RequestDate',
        'Description' => 'nullable|string|max:500',
        'SupportingDocument' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
    ]);

    $docPath = null;
    if ($request->hasFile('SupportingDocument')) {
        $docPath = $request->file('SupportingDocument')->store('endorsements', 'public');
    }

    DB::table('t_BancassurancePolicyEndorsements')->insert([
        'PolicyID' => $id,
        'EndorsementType' => $request->EndorsementType,
        'RequestDate' => $request->RequestDate,
        'EffectiveDate' => $request->EffectiveDate,
        'Description' => $request->Description,
        'SupportingDocumentPath' => $docPath,
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.policies.index')->with('success', 'Endorsement recorded successfully.');
}
public function endorsementList()
{
    $endorsements = DB::table('t_BancassurancePolicyEndorsements as e')
        ->leftJoin('t_BancassurancePolicies as p', 'e.PolicyID', '=', 'p.Id')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->select(
            'e.Id',
            'p.PolicyNumber',
            'c.FullName as CustomerName',
            'e.EndorsementType',
            'e.RequestDate',
            'e.EffectiveDate',
            'e.Description',
            'e.SupportingDocumentPath',
            'e.CreatedAt'
        )
        ->orderByDesc('e.Id')
        ->get();

    return view('bancassurance.policies.endorsements.index', compact('endorsements'));
}
public function register()
{
    $policies = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProviders as i', 'p.InsurerID', '=', 'i.Id')
        ->select(
            'p.Id',
            'p.PolicyNumber',
            'p.Status',
            'p.PolicyStartDate',
            'p.PolicyEndDate',
            'c.FullName as CustomerName',
            'i.Name as InsurerName'
        )
        ->whereIn('p.Status', ['Issued', 'Active'])
        ->orderByDesc('p.Id')
        ->get();

    return view('bancassurance.policies.register', compact('policies'));
}
public function renewalIndex()
{
    $policies = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->select('p.Id', 'p.PolicyNumber', 'c.FullName as CustomerName', 'p.PolicyEndDate', 'p.Status')
        ->where('p.Status', 'Issued')
        ->where('p.PolicyEndDate', '<=', now()->addMonths(2)) // Renewals within next 60 days
        ->orderBy('p.PolicyEndDate')
        ->get();

    return view('bancassurance.policies.renewals.index', compact('policies'));
}

public function initiateRenewal($id)
{
    $policy = DB::table('t_BancassurancePolicies')->where('Id', $id)->first();
    return view('bancassurance.policies.renewals.create', compact('policy'));
}
public function storeRenewal(Request $request, $id)
{
    $request->validate([
        'RenewalDate'   => 'required|date',
        'NewStartDate'  => 'required|date|after_or_equal:RenewalDate',
        'NewEndDate'    => 'required|date|after:NewStartDate',
        'Notes'         => 'nullable|string|max:500',
    ]);

    DB::table('t_BancassurancePolicyRenewals')->insert([
        'PolicyID'     => $id,
        'RenewalDate'  => $request->RenewalDate,
        'NewStartDate' => $request->NewStartDate,
        'NewEndDate'   => $request->NewEndDate,
        'Status'       => 'Requested',
        'Notes'        => $request->Notes,
        'CreatedBy'    => auth()->id(),
        'CreatedAt'    => now()
    ]);

    return redirect()->route('bancassurance.policies.renewals.index')
        ->with('success', 'Renewal request submitted successfully.');
}
public function show($id)
{
$policy = DB::table('t_BancassurancePolicies as p')
    ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
    ->leftJoin('t_InsuranceProviders as i', 'p.InsurerID', '=', 'i.Id')
    ->select(
        'p.Id',
        'p.PolicyNumber',
        'p.Status',
        'p.PolicyStartDate',
        'p.PolicyEndDate',
        'p.TotalPremium',
        'p.AmountPaid',
        'p.InstallmentAmount',
        'p.NextInstallmentDueDate',
        'p.PaymentFrequency',
        'c.FullName as CustomerName',
        'i.Name as InsurerName'
    )
    ->where('p.Id', $id)
    ->first();

    $installments = DB::table('t_BancassurancePremiumPayments as pp')
        ->leftJoin('t_Employees as e', 'pp.ReceivedBy', '=', 'e.Id')
        ->where('pp.PolicyID', $id)
        ->select('pp.*', DB::raw("CONCAT(e.FirstName, ' ', e.LastName) AS ReceiverName"))
        ->orderByDesc('pp.PaymentDate')
        ->get();

    return view('bancassurance.policies.show', compact('policy', 'installments'));
}

}
