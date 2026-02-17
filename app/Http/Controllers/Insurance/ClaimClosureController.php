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

        return view('bancassurance.claims.initiate_closure_form', compact('claims', 'status'));
    }

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
