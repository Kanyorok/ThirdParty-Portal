<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsurancePolicyStatus;
use App\Enums\Insurance\InsuranceReferralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassurancePolicyRequest;
use App\Http\Requests\Insurance\BancassuranceUnderwritingRequest;
use App\Http\Requests\Insurance\PolicyRenewalRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancassurancePremiumPayments;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProductRider;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\BancassurancePolicyService;
use App\Services\Insurance\BancassuranceUnderwritingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{

public function index(Request $request)
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $statuses = InsurancePolicyStatus::cases();

    $query = BancassurancePolicy::with(['customer.thirdParty', 'product', 'insurer'])
        ->when($request->status, fn($q) => $q->where('Status', $request->status))
        ->when($request->from, fn($q) => $q->whereDate('PolicyStartDate', '>=', $request->from))
        ->when($request->to, fn($q) => $q->whereDate('PolicyEndDate', '<=', $request->to))
        ->when($request->customer, function ($q) use ($request) {
            $q->whereHas('customer.thirdParty', fn($q2) => $q2->where('ThirdPartyName', 'like', '%' . $request->customer . '%'));
        })
        ->orderByDesc('Id')
        ->get();

    return view('bancassurance.policies.index', compact('query', 'statuses'))->with(['policies' => $query]);
}

public function create()
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $referrals = BancAssuranceReferral::with('customerreferral.thirdParty', 'referredByEmployee')->get();
    $customers = BancassuranceCustomer::all();
    $insurers = InsuranceProvider::all();
    $paymentfrequencys = CodeDetail::where('CodeID','PaymentFrequency')->get();

    return view('bancassurance.policies.create', compact(
        'customers', 'insurers', 'paymentfrequencys', 'referrals'));
}

// public function getReferralsByCustomer($customerId)
// {
//     $referrals = BancAssuranceReferral::with([
//             'customerreferral.thirdParty',
//             'referredByEmployee'
//         ])
//         ->where('ClientId', $customerId)
//         ->orderByDesc('Id')
//         ->get();

//     return response()->json($referrals);
// }
public function getReferralsByCustomer($customerId)
{
    $referrals = BancAssuranceReferral::with([
        'customerreferral.thirdParty',
        'referredByEmployee'
    ])
    ->where('ClientId', $customerId)
    ->orderByDesc('Id')
    ->get()
    ->map(function ($ref) {
        return [
            'id' => $ref->Id,
            'customer_name' => $ref->customerreferral?->thirdParty?->ThirdPartyName ?? 'Unknown Customer',
            'referred_by' => $ref->referredByEmployee?->Name ?? 'Unknown Staff',
            'Product' => $ref->insuranceProduct?->Name,
            'Insurer' => $ref->preferredInsurer?->Name,
        ];
    });

    return response()->json($referrals);
}



public function getProductsByInsurer($insurerId)
{
    $products = InsuranceProduct::where('InsuranceProviderID', $insurerId)->get();
    return response()->json($products);
}

public function getRiderAddOnsByProduct($productId)
{
    $rideraddons = InsuranceProductRider::where('Product', $productId)->get();
    return response()->json($rideraddons);
}

public function store(BancassurancePolicyRequest $request)
{
    $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);
    $validated = $request->validated();


    $CustomerId = BancassuranceCustomer::findOrFail($validated['CustomerID']);
    $ReferralID = isset($validated['ReferralID']) && $validated['ReferralID']? BancAssuranceReferral::find($validated['ReferralID']): null;
    $ProductId = InsuranceProduct::findOrFail($validated['ProductID'] ?? null);
    $InsurerId = InsuranceProvider::findOrFail($validated['InsurerID'] ?? null);
    $RiderAddOn = isset($validated['RiderAddOn']) && $validated['RiderAddOn'] ? InsuranceProductRider::find($validated['RiderAddOn']) : null;
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
        $ReferralID,
        $RiderAddOn,
        isset($validated['IssuedDate']) ? Carbon::parse($validated['IssuedDate']) : null,
        isset($validated['ExpiryDate']) ? Carbon::parse($validated['ExpiryDate']) : null,
        true,
        $Status,
        $request->user(),
    );

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy proposal submitted.');
}

public function print($id)
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);

    $policy = BancassurancePolicy::with([
        'customer.thirdParty',
        'insurer',
        'product',
        'referral.customerreferral.thirdParty',
        'referral.referredByEmployee',
        'riderAddOn',
    ])->findOrFail($id);

    return view('bancassurance.policies.print', compact('policy'));
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

// public function submitForUnderwriting(Request $request, $id)
// {
//     $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);

//     $validated = $request->validate([
//         'Document' => 'nullable|file|max:2048'
//     ]);

//     $document = $request->file('Document');

//     // Get policy as Eloquent model
//     $policy = BancassurancePolicy::find($id);

//     if (!$policy) {
//         return redirect()->back()->with('error', 'Policy not found.');
//     }

//     // Upload file + update ModifiedBy
//     $upload = BancassurancePolicyService::uploadpolicy(
//         $policy,
//         $request->user(),
//         $document
//     );

//     // Update policy status
//     $policy->update([
//         'Status'     => InsurancePolicyStatus::SubmittedForUnderwriting->value,
//         'ModifiedBy' => auth()->id(),
//         'ModifiedOn' => now()
//     ]);

//     return redirect()->route('bancassurance.policies.index')
//         ->with('success', 'Proposal submitted to underwriter.');
// }

public function submitForUnderwriting(request $request, $id)
    {
        $validated = $request->validate([
            'file' => 'required|array',
            'file.*' => [
                'file',
                Rule::file()->types(ExtensionsEnum::getAllMimeTypes())->max(9000),
            ],
        ]);


        $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);

        try {
            $policy = BancassurancePolicy::find($id);
            if (!$policy) {
                return response()->json(['error' => 'Policy not found.'], 404);
            }

            foreach ($request->file('file', []) as $uploadedFile) {
                BancassurancePolicyService::uploadpolicy(
                    $policy,
                    $request->user(),
                    $uploadedFile
                );
            }

            $policy->update([
                'Status'     => InsurancePolicyStatus::SubmittedForUnderwriting->value,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error submitting policy for underwriting: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error, try again later.'], 500);
        }

    return redirect()->route('bancassurance.policies.index')
    ->with('success', 'Proposal submitted to underwriter.');
        // Optionally add document summary if needed
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

public function register()
{
    $policies = BancassurancePolicy::with(['customer', 'insurer'])
        ->where('Status',InsurancePolicyStatus::Issued->value)
        ->get();

    return view('bancassurance.policies.register', compact('policies'));
}


public function renewalIndex()
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $policies = BancassurancePolicy::with('customer')
        ->where('Status', InsurancePolicyStatus::Issued->value)
        ->where('PolicyEndDate', '<=', now()->addMonths(2))
        ->whereDoesntHave('renewals')
        ->orderBy('PolicyEndDate')
        ->get();

    return view('bancassurance.policies.renewals.index', compact('policies'));
}

public function initiateRenewal($id)
{
    $this->authorize(PermissionEnum::BancassurancePolicyView, BancassurancePolicy::class);
    $policy = BancassurancePolicy::with('customer')->findOrFail($id);
    return view('bancassurance.policies.renewals.create', compact('policy'));
}
public function storeRenewal(PolicyRenewalRequest $request, $id)
{
    $this->authorize(PermissionEnum::BancassurancePolicyCreate, BancassurancePolicy::class);
    $validated = $request->validated();

    // Optionally, check if policy exists
    $policy = BancassurancePolicy::findOrFail($id);

    // Use Eloquent if you have a model for renewals, otherwise keep DB::table
    DB::table('t_BancassurancePolicyRenewals')->insert([
        'PolicyID'     => $id,
        'RenewalDate'  => $request->RenewalDate,
        'NewStartDate' => $request->NewStartDate,
        'NewEndDate'   => $request->NewEndDate,
        'Status'       => InsurancePolicyStatus::Issued->value,
        'Notes'        => $request->Notes,
        'CreatedBy'    => auth()->id(),
        'CreatedOn'    => now(),
        'ModifiedBy'   => auth()->id(),
        'ModifiedOn'   => now(),
    ]);

    return redirect()->route('bancassurance.policies.renewals.index')
        ->with('success', 'Renewal request submitted successfully.');
}

public function show($id)
{
    $policy = BancassurancePolicy::with(['customer','insurer'])->findOrFail($id);
    $installments = BancassurancePremiumPayments::where('PolicyID', $id)->get();

    return view('bancassurance.policies.show', compact('policy','installments'));
}
}
