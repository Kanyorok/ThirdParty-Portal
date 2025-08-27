<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsurancePolicyStatus;
use App\Enums\Insurance\InsuranceReferralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassurancePolicyRequest;
use App\Http\Requests\Insurance\BancassuranceUnderwritingRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\BancassurancePolicyService;
use App\Services\Insurance\BancassuranceUnderwritingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    // Policy Proposal
public function index(Request $request)
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
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

public function create()
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $referrals = BancAssuranceReferral::all();
    $customers = BancassuranceCustomer::all();
    $insurers = InsuranceProvider::all();
    $paymentfrequencys = CodeDetail::where('CodeID','PaymentFrequency')->get();

    return view('bancassurance.policies.create', compact(
        'customers', 'insurers', 'paymentfrequencys', 'referrals'));
}

public function getProductsByInsurer($insurerId)
{
    $products = InsuranceProduct::where('InsuranceProviderID', $insurerId)->get();
    return response()->json($products);
}


public function store(BancassurancePolicyRequest $request)
{
    $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);
    $validated = $request->validated();

    $CustomerId = BancassuranceCustomer::findOrFail($validated['CustomerID']);
    $referralId = isset($validated['ReferralID']) ? BancAssuranceReferral::find($validated['ReferralID']) : null;
    $ProductId = InsuranceProduct::findOrFail($validated['ProductID'] ?? null);
    $InsurerId = InsuranceProvider::findOrFail($validated['InsurerID'] ?? null);
    $Paymentfrquency = CodeDetail::findOrFail($validated['PaymentFrequency'] ?? null);
    $Status = InsurancePolicyStatus::Proposal;


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

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy proposal submitted.');
}




//Proposal Review
public function reviewIndex()
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $proposals = BancassurancePolicy::with(['customer', 'product'])
        ->whereIn('Status', [InsurancePolicyStatus::Proposal,InsurancePolicyStatus::SubmittedForUnderwriting])->get();

        return view('bancassurance.policies.review_index', compact('proposals'));
    }

public function review($id)
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $policy = BancassurancePolicy::with(['customer','product'])->find($id);

    if (!$policy) {
        return redirect()->route('bancassurance.policies.index')->with('error', 'Policy not found.');
    }
    
    return view('bancassurance.policies.review', compact('policy'));
}

public function submitForUnderwriting(Request $request, $id)
{
    $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);

    $validated = $request->validate([
        'Document' => 'nullable|file|max:2048'
    ]);

    $document = $request->file('Document');

    // Get policy as Eloquent model
    $policy = BancassurancePolicy::find($id);

    if (!$policy) {
        return redirect()->back()->with('error', 'Policy not found.');
    }

    // Upload file + update ModifiedBy
    $upload = BancassurancePolicyService::uploadpolicy(
        $policy,
        $request->user(),
        $document
    );

    // Update policy status
    $policy->update([
        'Status'     => InsurancePolicyStatus::SubmittedForUnderwriting->value,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    return redirect()->route('bancassurance.policies.index')
        ->with('success', 'Proposal submitted to underwriter.');
}





// Show the feedback form
public function feedbackForm($id) 
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
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
            'Status'     => InsurancePolicyStatus::AwaitingIssuance->value,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    } elseif ($Decision->Description === 'Decline') {
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

//Issue Policy
public function issuanceList()
{
    $policies = BancassurancePolicy::with(['customer','insurer','product'])
    ->where('Status', InsurancePolicyStatus::AwaitingIssuance->value)
    ->get();

    return view('bancassurance.policies.issuance-list', compact('policies'));
}


// public function issueForm($id)
// {
//     $policy = BancassurancePolicy::where('Id', $id)
//         ->where('Status', InsurancePolicyStatus::AwaitingIssuance->value)
//         ->firstOrFail();

//     return view('bancassurance.policies.issue', compact('policy'));
// }


// Store issuance details
public function storeIssuance($id)
{
    $Policy = BancassurancePolicy::find($id);
    $Policy->update([
        'Status'     => InsurancePolicyStatus::Issued->value,
        'ModifiedBy' => auth()->id(),
        'ModifiedOn' => now()
    ]);

    // Update related referral status to Converted
    if ($Policy->ReferralID) {
        $referral = BancAssuranceReferral::find($Policy->ReferralID);
        if ($referral) {
            $referral->Status = InsuranceReferralStatus::Converted->value;
            $referral->ModifiedBy = auth()->id();
            $referral->ModifiedOn = now();
            $referral->save();
        }
    }

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy issued successfully.');
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
