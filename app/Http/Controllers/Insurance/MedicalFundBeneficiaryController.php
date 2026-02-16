<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundBeneficiaryRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundBeneficiary;
use App\Models\Insurance\MedicalFundContributor;
use App\Services\Insurance\MedicalFundBeneficiaryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MedicalFundBeneficiaryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display all beneficiaries under a given contributor.
     */
    public function index(MedicalFundContributor $contributor)
    {
        $beneficiaries = $contributor->beneficiaries()
            ->orderByDesc('Id')
            ->paginate(10);

        $medical_fund = $contributor->fund;


        return view('bancassurance.medical_fund_beneficiaries.index', compact(
            'beneficiaries',
            'contributor',
            'medical_fund'
        ));
    }

    /**
     * Show form for creating a new beneficiary.
     */
    public function create(MedicalFundContributor $contributor)
    {
        $medical_fund = $contributor->fund;

        $relationships = CodeDetail::where('CodeID', 'BeneficiaryRelationship')
            ->orderBy('Description')
            ->get(['ID', 'Description']);

        return view('bancassurance.medical_fund_beneficiaries.create', compact(
            'contributor',
            'medical_fund',
            'relationships'
        ));
    }

    /**
     * Store a new beneficiary.
     */
    public function store(MedicalFundBeneficiaryRequest $request, MedicalFund $medicalFund)
    {
        $validated = $request->validated();

        $dateOfBirth = $request->filled('DateOfBirth')
            ? Carbon::parse($request->DateOfBirth)
            : null;

        $relationship = CodeDetail::find($validated['Relationship'] ?? null);

        // Use the MedicalFund provided by route-model binding
        $fund = $medicalFund;

        // Ensure we use the validated key name for NationalID (consistent with the request rules)
        $nationalId = $validated['NationalID'] ?? $validated['NationalId'] ?? null;

        // Create via service
        $service = MedicalFundBeneficiaryService::create(
            $fund,
            $validated['FullName'],
            $relationship,
            $dateOfBirth,
            $nationalId,
            $validated['Contact'] ?? null,
            $request->boolean('IsActive', true),
            Auth::user()
        );

        // If ContributorId was passed from the form (e.g., contributor's show page), attach it
        if (! empty($validated['ContributorId'])) {
            $service->medicalFundBeneficiary->update(['ContributorId' => $validated['ContributorId']]);
        }

        return redirect()
            ->route('bancassurance.medicalfunds.show', $fund->Id)
            ->with('success', 'Beneficiary added successfully.');
    }

    /**
     * Show form for editing a beneficiary.
     */
    public function edit(MedicalFundBeneficiary $beneficiary)
    {
        $contributor = $beneficiary->contributor;
        $medical_fund = $contributor->fund;

        $relationships = CodeDetail::where('CodeID', 'BeneficiaryRelationship')
            ->orderBy('Description')
            ->get(['ID', 'Description']);

        return view('bancassurance.medical_fund_beneficiaries.edit', compact(
            'beneficiary',
            'contributor',
            'medical_fund',
            'relationships'
        ));
    }

    /**
     * Update a beneficiary record.
     */
    public function update(MedicalFundBeneficiaryRequest $request, MedicalFundBeneficiary $beneficiary)
    {
        $validated = $request->validated();

        $dateOfBirth = $request->filled('DateOfBirth')
            ? Carbon::parse($request->DateOfBirth)
            : null;

        $relationship = CodeDetail::find($validated['RelationshipID']);

        // ✅ Create service wrapper for this beneficiary
        $service = new MedicalFundBeneficiaryService($beneficiary);

        $service->update(
            $validated['FullName'],
            $relationship,
            $dateOfBirth,
            $validated['NationalId'] ?? null,
            $validated['Contact'] ?? null,
            $request->boolean('IsActive', true),
            Auth::user()
        );

        return redirect()
            ->route('bancassurance.contributors.show', $beneficiary->ContributorId)
            ->with('success', 'Beneficiary updated successfully.');
    }

    /**
     * Delete a beneficiary.
     */
    public function destroy(MedicalFundBeneficiary $beneficiary)
    {
        $contributorId = $beneficiary->ContributorId;

        $service = new MedicalFundBeneficiaryService($beneficiary);
        $service->delete(Auth::user());

        return redirect()
            ->route('bancassurance.contributors.show', $contributorId)
            ->with('success', 'Beneficiary deleted successfully.');
    }
}
