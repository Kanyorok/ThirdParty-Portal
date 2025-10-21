<?php

namespace App\Http\Controllers\Insurance;



use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFundContributor;
use App\Models\Insurance\MedicalFundBeneficiary;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributorBeneficiaryController extends Controller
{
    public function __construct(){ $this->middleware(['auth']); }

// App\Http\Controllers\Bancassurance\ContributorBeneficiaryController.php
public function store(Request $request, \App\Models\Insurance\MedicalFundContributor $contributor)
{
    $data = $request->validate([
        'FullName'     => ['required','string','max:255'],
        'Relationship' => ['required','string','max:50'],
        'DateOfBirth'  => ['nullable','date'],
        'NationalID'   => ['nullable','string','max:50'],
        'Contact'      => ['nullable','string','max:50'],
        'IsActive'     => ['nullable','boolean'],
    ]);

    $data['ContributorID'] = $contributor->ID;

    // keep FundID if column exists
    if (Schema::hasColumn('t_MedicalFundBeneficiaries', 'FundID')) {
        $data['FundID'] = $contributor->FundID;
    }

    \App\Models\Insurance\MedicalFundBeneficiary::create($data);

    return redirect()
        ->route('bancassurance.contributors.show', $contributor->ID)
        ->with('success','Beneficiary added.');
}
}
