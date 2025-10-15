<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundDisbursement;
use App\Models\Insurance\MedicalFundBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalFundDisbursementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // /bancassurance/medical-funds/{medical_fund}/disbursements
    public function index(MedicalFund $medical_fund)
    {
        $disbursements = $medical_fund->disbursements()
            ->with('beneficiary')
            ->orderBy('DisbursementDate','desc')
            ->paginate(20);

        $totals = [
            'sum' => $medical_fund->disbursements()->sum('Amount')
        ];

        return view('bancassurance.medical_fund_disbursements.index', compact('medical_fund','disbursements','totals'));
    }

    public function create(MedicalFund $medical_fund)
    {
        $beneficiaries = $medical_fund->beneficiaries()->where('IsActive',1)->orderBy('FullName')->get(['ID','FullName']);
        return view('bancassurance.medical_fund_disbursements.create', compact('medical_fund','beneficiaries'));
    }

    public function store(Request $request, MedicalFund $medical_fund)
    {
        $data = $request->validate([
            'BeneficiaryID'     => ['required','integer'],
            'DisbursementDate'  => ['required','date'],
            'Amount'            => ['required','numeric','min:0.01'],
            'Purpose'           => ['nullable','string','max:500'],
        ]);

        $data['FundID'] = $medical_fund->ID;
        $data['ApprovedBy'] = Auth::id();
        $data['ApprovedOn'] = now();

        MedicalFundDisbursement::create($data);

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', $medical_fund->ID)
            ->with('success','Disbursement recorded.');
    }

    public function edit(MedicalFundDisbursement $disbursement)
    {
        $medical_fund = $disbursement->fund;
        $beneficiaries = $medical_fund->beneficiaries()->where('IsActive',1)->orderBy('FullName')->get(['ID','FullName']);

        return view('bancassurance.medical_fund_disbursements.edit', compact('disbursement','medical_fund','beneficiaries'));
    }

    public function update(Request $request, MedicalFundDisbursement $disbursement)
    {
        $data = $request->validate([
            'BeneficiaryID'     => ['required','integer'],
            'DisbursementDate'  => ['required','date'],
            'Amount'            => ['required','numeric','min:0.01'],
            'Purpose'           => ['nullable','string','max:500'],
        ]);

        $disbursement->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', $disbursement->FundID)
            ->with('success','Disbursement updated.');
    }

    public function destroy(MedicalFundDisbursement $disbursement)
    {
        $fundId = $disbursement->FundID;
        $disbursement->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', $fundId)
            ->with('success','Disbursement deleted.');
    }
}
