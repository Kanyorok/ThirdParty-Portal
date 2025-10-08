<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundContributionRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContribution;
use App\Models\ThirdParty\ThirdParties;
use App\Services\Insurance\MedicalFundContributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MedicalFundContributionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(MedicalFund $medical_fund)
    {
        $contributions = $medical_fund->contributions()->orderBy('ContributionDate','desc')->paginate(20);
        $totals = [
            'sum' => $medical_fund->contributions()->sum('Amount')
        ];
        return view('bancassurance.medical_fund_contributions.index', compact('medical_fund','contributions','totals'));
    }

    public function create(MedicalFund $medical_fund)
    {
        $contributortypes = CodeDetail::where('CodeID', 'ContributorType')->get();
        $contributors = ThirdParties::orderBy('ThirdPartyName')->get(['Id','ThirdPartyName']);
        return view('bancassurance.medical_fund_contributions.create', compact('medical_fund','contributortypes','contributors'));
    }

    public function store(MedicalFundContributionRequest $request, MedicalFund $medical_fund)
    {
        $validated = $request->validated();

    // Ensure we use the validated keys safely. Use null-coalescing to avoid undefined array key notices.
    $FundId = MedicalFund::findOrFail($medical_fund->Id);
    $ContributorType = CodeDetail::findOrFail($validated['ContributorType']);
    $contributorIdKey = $validated['ContributorId'] ?? null;
    $ContributorId = $contributorIdKey ? ThirdParties::find($contributorIdKey) : null;

        $contribution = MedicalFundContributionService::create(
            $FundId,
            $ContributorType,
            $ContributorId,
            $validated['Amount'],
            Carbon::parse($validated['ContributionDate']),
            $validated['Notes'] ?? null,
            Auth::user(),
        );

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $medical_fund->Id)
            ->with('success','Contribution recorded.');
    }

    public function edit(MedicalFundContribution $contribution)
    {
        $medical_fund = $contribution->fund;
        return view('bancassurance.medical_fund_contributions.edit', compact('contribution','medical_fund'));
    }

    public function update(Request $request, MedicalFundContribution $contribution)
    {
        $data = $request->validate([
            'ContributorType'  => ['required','string','max:50'],
            'ContributorID'    => ['nullable','integer'],
            'Amount'           => ['required','numeric','min:0.01'],
            'ContributionDate' => ['required','date'],
            'Notes'            => ['nullable','string','max:500'],
        ]);

        $contribution->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $contribution->FundID)
            ->with('success','Contribution updated.');
    }

    public function destroy(MedicalFundContribution $contribution)
    {
        $fundId = $contribution->FundID;
        $contribution->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $fundId)
            ->with('success','Contribution deleted.');
    }
}
