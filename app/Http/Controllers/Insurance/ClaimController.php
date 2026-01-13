<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsurancePolicyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\BancassuranceClaimAssessmentRequest;
use App\Http\Requests\Insurance\BancassuranceClaimRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimAssessment;
use App\Models\Insurance\BancassurancePolicy;
use App\Services\Insurance\BancassuranceClaimAssessmentService;
use App\Services\Insurance\BancassuranceClaimService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    //
    public function create()
    {
        $this->authorize(PermissionEnum::BancassuranceClaimView, BancassuranceClaim::class);
        $policies = BancassurancePolicy::where('Status', InsurancePolicyStatus::Issued)->get();
        $claimtypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();
        $claimstatus = CodeDetail::where('CodeID', 'ClaimStatus')->get();
        $currencies = Currency::all();

        return view('bancassurance.claims.create', compact('policies', 'claimtypes', 'claimstatus', 'currencies'));
    }

    public function store(BancassuranceClaimRequest $request)
    {
        $this->authorize(PermissionEnum::BancassuranceClaimCreate, BancassuranceClaim::class);
        $validated = $request->validated();
        $PolicyId = BancassurancePolicy::findOrFail($validated['PolicyId']);
        $Status = CodeDetail::where('CodeID', 'ClaimStatus')->where('Value', 'I')->firstOrFail();
        $ClaimType = CodeDetail::findOrFail($validated['ClaimType']);
        $Currency = Currency::findOrFail($validated['CurrencyId']);

        foreach ($request->file('file', []) as $uploadedFile) {
        $claim = BancassuranceClaimService::create(
            $PolicyId,
            $ClaimType,
            $validated['ClaimReason'],
            $validated['ClaimAmount'],
            $Currency,
            Carbon::parse($validated['ClaimDate']),
            $Status,
            $request->user(),
            $uploadedFile
        );
        }

        return redirect()->route('bancassurance.claims.index')->with('success', 'Claim initiated successfully.');
    }

    public function index(Request $request)
    {
        $mode = $request->query('mode', 'default');

        $claims = BancassuranceClaim::all();

        return view('bancassurance.claims.index', compact('claims', 'mode'));
    }

    public function assessForm($id)
    {
        $this->authorize(PermissionEnum::BancassuranceClaimAssessmentView, BancassuranceClaimAssessment::class);
        $claim = BancassuranceClaim::find($id);
        $decisions = CodeDetail::where('CodeID', 'Decision')->get();

        if (!$claim) {
            return redirect()->route('bancassurance.claims.index')->with('error', 'Claim not found.');
        }

        return view('bancassurance.claims.assess', compact('claim', 'decisions'));
    }


    public function storeAssessment(BancassuranceClaimAssessmentRequest $request, $id)
    {
        $this->authorize(PermissionEnum::BancassuranceClaimAssessmentCreate, BancassuranceClaimAssessment::class);
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


    public function assessmentlist()
    {
        $assessments = BancassuranceClaimAssessment::all();

        return view('bancassurance.claims.assessment_list', compact('assessments'));
    }

    public function assessmentshow($id)
    {
        $this->authorize(PermissionEnum::BancassuranceClaimAssessmentView, BancassuranceClaimAssessment::class);
        $assessment = BancassuranceClaimAssessment::findOrFail($id);
        return view('bancassurance.claims.assessment_show', compact('assessment'));
    }

    public function assessmentedit($id)
    {
        $assessment = BancassuranceClaimAssessment::findOrFail($id);
        $decisions = CodeDetail::where('CodeID', 'Decision')->get();
        return view('bancassurance.claims.assessment_edit', compact('assessment', 'decisions'));
    }

    public function assessmentupdate(BancassuranceClaimAssessmentRequest $request, $id)
    {
        $this->authorize(PermissionEnum::BancassuranceClaimAssessmentUpdate, BancassuranceClaimAssessment::class);
        $validated = $request->validated();

        $assessment = BancassuranceClaimAssessment::findOrFail($id);

        if ($assessment->claimpaiyments()->exists()) {
            return redirect()->back()
            ->withErrors(['error' => 'This claim has been paid cannot be modified.']);
        }
         
        $Decision = CodeDetail::findOrFail($validated['Decision']);

        $assessment = BancassuranceClaimAssessmentService::update(
            $assessment,
            $validated['AssessmentComments'],
            $validated['AssessmentAmount'],
            $Decision,
            $request->user(),
        );


        return redirect()->route('bancassurance.claims.assessment_list')
            ->with('success', 'Claim assessment updated successfully.');
    }
}
