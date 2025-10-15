<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundBeneficiary;
use Illuminate\Http\Request;

class MedicalFundBeneficiaryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // Nested index: /bancassurance/medical-funds/{medical_fund}/beneficiaries
    public function index(MedicalFund $medical_fund)
    {
        $beneficiaries = $medical_fund->beneficiaries()->orderBy('ID','desc')->paginate(20);
        return view('bancassurance.medical_fund_beneficiaries.index', compact('medical_fund','beneficiaries'));
    }

    public function create(MedicalFund $medical_fund)
    {
        return view('bancassurance.medical_fund_beneficiaries.create', compact('medical_fund'));
    }

    public function store(Request $request, MedicalFund $medical_fund)
    {
        $data = $request->validate([
            'FullName'     => ['required','string','max:255'],
            'Relationship' => ['nullable','string','max:50'],
            'DateOfBirth'  => ['nullable','date'],
            'NationalID'   => ['nullable','string','max:50'],
            'Contact'      => ['nullable','string','max:50'],
            'IsActive'     => ['nullable','boolean'],
        ]);

        $data['FundID'] = $medical_fund->ID;
        MedicalFundBeneficiary::create($data);

        return redirect()
            ->route('bancassurance.medicalfunds.beneficiaries.index', $medical_fund->ID)
            ->with('success','Beneficiary added.');
    }

    // Shallow routes below (beneficiary param only)
    public function edit(MedicalFundBeneficiary $beneficiary)
    {
        $medical_fund = $beneficiary->fund;
        return view('bancassurance.medical_fund_beneficiaries.edit', compact('beneficiary','medical_fund'));
    }

    public function update(Request $request, MedicalFundBeneficiary $beneficiary)
    {
        $data = $request->validate([
            'FullName'     => ['required','string','max:255'],
            'Relationship' => ['nullable','string','max:50'],
            'DateOfBirth'  => ['nullable','date'],
            'NationalID'   => ['nullable','string','max:50'],
            'Contact'      => ['nullable','string','max:50'],
            'IsActive'     => ['nullable','boolean'],
        ]);

        $beneficiary->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.beneficiaries.index', $beneficiary->FundID)
            ->with('success','Beneficiary updated.');
    }

    public function destroy(MedicalFundBeneficiary $beneficiary)
    {
        $fundId = $beneficiary->FundID;
        $beneficiary->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.beneficiaries.index', $fundId)
            ->with('success','Beneficiary removed.');
    }
}
