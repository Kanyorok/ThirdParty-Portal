<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\Disputes\LegalCounsel;
use App\Models\Legal\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCounselController extends Controller
{
    public function index()
    {
        $counsels = LegalCounsel::with('case')->orderByDesc('CreatedOn')->get();
        return view('legal.disputes.counsels.index', compact('counsels'));
    }

    public function create(Request $request)
    {
        $caseId = $request->get('case_id');
        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.counsels.create', compact('case'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,ID',
            'CounselName' => 'required|string|max:255',
            'LawFirm' => 'nullable|string|max:255',
            'ContactEmail' => 'nullable|email|max:255',
            'ContactPhone' => 'nullable|string|max:50',
            'Role' => 'nullable|string|max:100',
            'Remarks' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();

        LegalCounsel::create($data);

        return redirect()->route('legal.cases.show', $data['LegalCaseID'])->with('success', 'Counsel assigned successfully.');
    }

    public function edit($id)
    {
        $counsel = LegalCounsel::findOrFail($id);
        $case = LegalCase::findOrFail($counsel->LegalCaseID);

        return view('legal.disputes.counsels.edit', compact('counsel', 'case'));
    }

    public function update(Request $request, $id)
    {
        $counsel = LegalCounsel::findOrFail($id);

        $data = $request->validate([
            'CounselName' => 'required|string|max:255',
            'LawFirm' => 'nullable|string|max:255',
            'ContactEmail' => 'nullable|email|max:255',
            'ContactPhone' => 'nullable|string|max:50',
            'Role' => 'nullable|string|max:100',
            'Remarks' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $counsel->update($data);

        return redirect()->route('legal.cases.show', $counsel->LegalCaseID)->with('success', 'Counsel updated successfully.');
    }

    public function show($id)
    {
        $counsel = LegalCounsel::with('case')->findOrFail($id);
        return view('legal.disputes.counsels.show', compact('counsel'));
    }
}
