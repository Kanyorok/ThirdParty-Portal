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


}
