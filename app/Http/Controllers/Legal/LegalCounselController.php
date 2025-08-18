<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseCounsel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCounselController extends Controller
{
    public function index($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        $counsels = LegalCaseCounsel::where('LegalCaseID', $caseId)->get();

        return view('legal.disputes.counsels.index', compact('case', 'counsels'));
    }

    public function create($caseId)
    {

        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.counsels.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        $validated = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,Id',
            'CounselName' => 'required|string|max:255',
            'FirmName' => 'nullable|string|max:255',
            'Email' => 'nullable|email|max:255',
            'Phone' => 'nullable|string|max:50',
            'Role' => 'nullable|string|max:100',
            // 'Remarks' => 'nullable|string',
        ]);

        $counsel =  LegalCaseCounsel::create([
            'LegalCaseID' => $caseId,
            'CounselName' => $validated['CounselName'],
            'FirmName' => $validated['FirmName'],
            'Email' => $validated['Email'],
            'Phone' => $validated['Phone'],
            'Role' => $validated['Role'],
            // 'Remarks' => $validated['Remarks'],
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.show', $caseId)->with('success', 'Counsel assigned successfully.');
    }

    public function edit($disputeId, $counselId)
{
    $case = LegalCase::findOrFail($disputeId);
    $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                               ->findOrFail($counselId);

    return view('legal.disputes.counsels.edit', compact('case', 'counsel'));
}

public function update(Request $request, $disputeId, $counselId)
{
    $case = LegalCase::findOrFail($disputeId);
    $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                               ->findOrFail($counselId);

    $data = $request->validate([
        'CounselName' => 'required|string|max:255',
        'FirmName'    => 'nullable|string|max:255',
        'Email'       => 'nullable|email|max:255',
        'Phone'       => 'nullable|string|max:50',
        'Role'        => 'nullable|string|max:100',
        'Remarks'     => 'nullable|string',
    ]);

    $data['ModifiedBy'] = Auth::id();
    $data['ModifiedOn'] = now();

    $counsel->update($data);

    return redirect()
        ->route('legal.disputes.counsels.show', [$case->Id, $counsel->Id])
        ->with('success', 'Counsel updated successfully.');
}


    public function show($disputeId, $counselId)
{
    $case = LegalCase::findOrFail($disputeId);
    $counsel = LegalCaseCounsel::where('LegalCaseID', $disputeId)
                               ->findOrFail($counselId);

    return view('legal.disputes.counsels.show', compact('case', 'counsel'));
}


    public function destroy($id)
    {
        $counsel = LegalCaseCounsel::findOrFail($id);
        $caseId = $counsel->LegalCaseID;

        $counsel->DeletedBy = Auth::id();
        $counsel->save();
        $counsel->delete();

        return redirect()->route('legal.cases.show', $caseId)->with('success', 'Counsel removed successfully.');
    }
}
