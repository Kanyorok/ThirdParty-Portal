<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use App\Enums\Insurance\InsuranceReferralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassurancePolicyRequest;
use App\Http\Requests\Insurance\BancassuranceUnderwritingRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceUnderwriting;
use App\Services\Insurance\BancassurancePolicyService;
use App\Services\Insurance\BancassuranceUnderwritingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    //
public function create(Request $request)
{
    $referrals = BancAssuranceReferral::all();
    $customers = BancassuranceCustomers::all();
    $products = CodeDetail::where('CodeID', 'InsuranceProduct')->get();
    $insurers = CodeDetail::where('CodeID', 'InsuranceProvider')->get();
    $paymentfrequencys = CodeDetail::where('CodeID','PaymentFrequency')->get();

    return view('bancassurance.policies.create', compact(
        'customers', 'products', 'insurers', 'paymentfrequencys', 'referrals'));
}


public function store(BancassurancePolicyRequest $request)
{
    $validated = $request->validated();

    $CustomerId = BancassuranceCustomers::findOrFail($validated['CustomerID']);
    $referralId = BancAssuranceReferral::findOrFail($validated['ReferralID'] ?? null);
    $ProductId = CodeDetail::findOrFail($validated['ProductID'] ?? null);
    $InsurerId = CodeDetail::findOrFail($validated['InsurerID'] ?? null);
    $Paymentfrquency = CodeDetail::findOrFail($validated['PaymentFrequency'] ?? null);
    $Status = InsurancePolicyStatus::from($validated['Status']);


   $policy = BancassurancePolicyService::create(
        $CustomerId,
        $ProductId,
        $InsurerId,
        $validated['SumAssured'],
        $validated['PremiumAmount'],
        Carbon::parse($validated['PolicyStartDate']),
        Carbon::parse($validated['PolicyEndDate']),
        $Paymentfrquency,
        $referralId,
        isset($validated['IssuedDate']) ? Carbon::parse($validated['IssuedDate']) : null,
        isset($validated['ExpiryDate']) ? Carbon::parse($validated['ExpiryDate']) : null,
        true,
        $Status,
        $request->user(),
    );

    // Optional: Update referral status
    if (
        $request->filled('ReferralID') &&
        in_array($Status, [InsurancePolicyStatus::Issued])
    ) {
        DB::table('t_BancassuranceReferrals')->where('Id', $request->ReferralID)->update([
            'Status' => InsuranceReferralStatus::Converted->value,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    }

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy proposal submitted.');
}

public function index(Request $request)
{
    $statuses = InsurancePolicyStatus::cases();

    $query = BancassurancePolicy::with(['customer', 'product', 'insurer'])
        ->when($request->status, fn($q) => $q->where('Status', $request->status))
        ->when($request->from, fn($q) => $q->whereDate('PolicyStartDate', '>=', $request->from))
        ->when($request->to, fn($q) => $q->whereDate('PolicyEndDate', '<=', $request->to))
        ->when($request->customer, function ($q) use ($request) {
            $q->whereHas('customer', fn($q2) =>
                $q2->where('FullName', 'like', '%' . $request->customer . '%'));
        })
        ->orderByDesc('Id')
        ->get();

    return view('bancassurance.policies.index', compact('query', 'statuses'))->with(['policies' => $query]);
}



public function submitForUnderwriting(Request $request, $id)
{
    $request = $request->validate([
        'Document' => 'nullable|file|max:2048'
    ]);

    $document = $request->file('Document');

    $PolicyId = DB::table('t_BancassurancePolicies')->where('Id', $id)->first();

    if (!$PolicyId) {
        return redirect()->back()->with('error', 'Policy not found.');
    }

    // Upload files and save paths
    $upload = BancassurancePolicyService::uploadpolicy(
        $PolicyId,
        $request->user(),
        $document
    );

    // Send email to underwriter

    // Update policy status
    DB::table('t_BancassurancePolicies')->where('Id', $id)->update([
        'Status' => InsurancePolicyStatus::SubmittedForUnderwriting->value,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.policies.index')->with('success', 'Proposal submitted to underwriter.');
}


public function reviewIndex()
{
    $proposals = BancassurancePolicy::with(['customer', 'product'])
        ->whereIn('Status', [InsurancePolicyStatus::Proposal,InsurancePolicyStatus::SubmittedForUnderwriting])->get();

    return view('bancassurance.policies.review_index', compact('proposals'));
}

public function review($id)
{
    $policy = BancassurancePolicy::with(['customer','product'])->find($id);

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
    }
    
    return view('bancassurance.policies.review', compact('policy'));
}


// Show the feedback form
public function feedbackForm($id) 
{
    $policy = BancassurancePolicy::with('customer')
        ->find($id);

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')
            ->with('error', 'Policy not found.');
    }

    // Get CodeDetails for dropdowns using Eloquent
    $decisions = CodeDetail::where('CodeID', 'Decision')->get();

    return view('bancassurance.policies.feedback', compact('policy', 'decisions'));
}

// public function feedbackForm($id)
// {
//     $policy = DB::table('t_BancassurancePolicies as p')
//         ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
//         ->leftJoin('t_CodeDetails as prod', 'p.ProductID', '=', 'prod.Id')
//         ->leftJoin('t_CodeDetails as ip', 'p.InsurerID', '=', 'ip.Id')
//         ->select(
//             'p.Id',
//             'p.PolicyNumber',
//             'p.Status',
//             'c.FullName as CustomerName',
//         )
//         ->where('p.Id', $id)
//         ->first();

//     if (!$policy) {
//         return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
//     }

//     return view('bancassurance.policies.feedback', compact('policy'));
// }

public function storeFeedback(BancassuranceUnderwritingRequest $request, $id)
{
    $validated = $request->validated();

    // Check if policy exists before inserting
    $Policy = BancassurancePolicy::find($id);
    if (!$Policy) {
        return redirect()->route('bancassurance.policies.index')
            ->with('error', 'Policy not found for feedback.');
    }

    // Get the decision record
    $Decision = CodeDetail::findOrFail($validated['Decision']);

    // Create underwriting feedback
    $underwriting = BancassuranceUnderwritingService::create(
        $Policy,
        Carbon::parse($validated['FeedbackDate']),
        (int) $validated['RiskScore'],
        $Decision,
        $validated['Comments'],
        $request->user()
    );

    // Update policy status based on decision
    if ($Decision->Description === 'Approved') {
        $Policy->update([
            'Status'     => InsurancePolicyStatus::Issued->value,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    } elseif ($Decision->Description === 'Rejected') {
        $Policy->update([
            'Status'     => InsurancePolicyStatus::Rejected->value,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    }
    // else → do nothing if decision is something else

    return redirect()->route('bancassurance.policies.index')
        ->with('success', 'Underwriting feedback recorded successfully.');
}


public function feedbackList()
{
    $proposals = BancassurancePolicy::with(['customer', 'product','insurer'])
        ->where('Status', InsurancePolicyStatus::SubmittedForUnderwriting->value)
        ->get();

    return view('bancassurance.policies.feedback-list', compact('proposals'));
}

// Show issuance form
// public function issueForm($id)
// {
//     $policy = DB::table('t_BancassurancePolicies as p')
//         ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
//         ->select('p.*', 'c.FullName as CustomerName')
//         ->where('p.Id', $id)
//         ->first();

//     if (!$policy || $policy->Status !== 'ApprovedForIssuance') {
//         return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not eligible for issuance.');
//     }

//     return view('bancassurance.policies.issue', compact('policy'));
// }

// // Store issuance details
// public function storeIssuance(Request $request, $id)
// {
//     $request->validate([
//         'IssuedDate' => 'required|date',
//         'ExpiryDate' => 'required|date|after:IssuedDate',
//         'PolicyNumber' => 'required|string|max:50',
//         'PolicyDocument' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
//     ]);

//     $filePath = null;
//     if ($request->hasFile('PolicyDocument')) {
//         $filePath = $request->file('PolicyDocument')->store('policies', 'public');
//     }

//     DB::table('t_BancassurancePolicies')->where('Id', $id)->update([
//         'PolicyNumber' => $request->PolicyNumber,
//         'IssuedDate' => $request->IssuedDate,
//         'ExpiryDate' => $request->ExpiryDate,
//         'PolicyDocumentPath' => $filePath,
//         'IsIssued' => 1,
//         'Status' => 'Issued',
//         'ModifiedBy' => auth()->id(),
//         'ModifiedOn' => now()
//     ]);

//     return redirect()->route('bancassurance.policies.index')->with('success', 'Policy issued successfully.');
// }
public function issuanceList()
{
    $policies = BancassurancePolicy::with(['customer','insurer','product'])
    ->where('Status', InsurancePolicyStatus::Issued->value)
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

// public function storeEndorsement(Request $request, $id)
// {
//     $request->validate([
//         'EndorsementType' => 'required|string|max:100',
//         'RequestDate' => 'required|date',
//         'EffectiveDate' => 'required|date|after_or_equal:RequestDate',
//         'Description' => 'nullable|string|max:500',
//         'SupportingDocument' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
//     ]);

//     $docPath = null;
//     if ($request->hasFile('SupportingDocument')) {
//         $docPath = $request->file('SupportingDocument')->store('endorsements', 'public');
//     }

//     DB::table('t_BancassurancePolicyEndorsements')->insert([
//         'PolicyID' => $id,
//         'EndorsementType' => $request->EndorsementType,
//         'RequestDate' => $request->RequestDate,
//         'EffectiveDate' => $request->EffectiveDate,
//         'Description' => $request->Description,
//         'SupportingDocumentPath' => $docPath,
//         'CreatedBy' => auth()->id(),
//         'CreatedAt' => now(),
//     ]);

//     return redirect()->route('bancassurance.policies.index')->with('success', 'Endorsement recorded successfully.');
// }
// public function endorsementList()
// {
//     $endorsements = DB::table('t_BancassurancePolicyEndorsements as e')
//         ->leftJoin('t_BancassurancePolicies as p', 'e.PolicyID', '=', 'p.Id')
//         ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
//         ->select(
//             'e.Id',
//             'p.PolicyNumber',
//             'c.FullName as CustomerName',
//             'e.EndorsementType',
//             'e.RequestDate',
//             'e.EffectiveDate',
//             'e.Description',
//             'e.SupportingDocumentPath',
//             'e.CreatedAt'
//         )
//         ->orderByDesc('e.Id')
//         ->get();

//     return view('bancassurance.policies.endorsements.index', compact('endorsements'));
// }
public function register()
{
    $policies = BancassurancePolicy::with(['customer', 'insurer'])
        ->where('Status',InsurancePolicyStatus::Issued->value)
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

// public function initiateRenewal($id)
// {
//     $policy = DB::table('t_BancassurancePolicies')->where('Id', $id)->first();
//     return view('bancassurance.policies.renewals.create', compact('policy'));
// }
// public function storeRenewal(Request $request, $id)
// {
//     $request->validate([
//         'RenewalDate'   => 'required|date',
//         'NewStartDate'  => 'required|date|after_or_equal:RenewalDate',
//         'NewEndDate'    => 'required|date|after:NewStartDate',
//         'Notes'         => 'nullable|string|max:500',
//     ]);

//     DB::table('t_BancassurancePolicyRenewals')->insert([
//         'PolicyID'     => $id,
//         'RenewalDate'  => $request->RenewalDate,
//         'NewStartDate' => $request->NewStartDate,
//         'NewEndDate'   => $request->NewEndDate,
//         'Status'       => 'Requested',
//         'Notes'        => $request->Notes,
//         'CreatedBy'    => auth()->id(),
//         'CreatedAt'    => now()
//     ]);

//     return redirect()->route('bancassurance.policies.renewals.index')
//         ->with('success', 'Renewal request submitted successfully.');
// }
public function show($id)
{
    $policy = BancassurancePolicy::with(['customer','insurer'])->findOrFail($id);

    return view('bancassurance.policies.show', ['policy' => $policy,'installments' => $policy]);
}


}
