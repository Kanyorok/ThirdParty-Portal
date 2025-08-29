<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseController extends Controller
{
    public function index()
    {
        $cases = LegalCase::all();
        return view('legal.disputes.index', compact('cases'));
    }

    public function create()
    {
        return view('legal.disputes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CaseTitle'=> 'required|string',
            'CaseNumber'=> 'required|string',
            'CourtName'=> 'required|string',
            'FilingDate'=> 'required|date',
            'OpposingParty'=> 'required|string',
            'CaseType'=> 'required|string',
            'Summary'=> 'required|string',
            'AssignedCounselID'=> 'nullable',
            'CaseDMSDocID'=> 'nullable',
        ]);

        LegalCase::create([
            'CaseTitle' => $validated['CaseTitle'],
            'CaseNumber' => $validated['CaseNumber'],
            'CourtName' => $validated['CourtName'],
            'FilingDate' => $validated['FilingDate'],
            'OpposingParty' => $validated['OpposingParty'],
            'CaseType' => $validated['CaseType'],
            'Summary' => $validated['Summary'],
            'AssignedCounselID' => $validated['AssignedCounselID']??null,
            'CaseDMSDocID' => $validated['CaseDMSDocID'] ?? null,
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case created successfully.');
    }

    public function edit($id)
    {
        $case = LegalCase::findOrFail($id);
        return view('legal.disputes.edit', compact('case'));
    }

    public function update(Request $request, $id)
    {
        $case = LegalCase::findOrFail($id);
        $case->update([
            'CaseTitle' => $request->CaseTitle,
            'CaseNumber' => $request->CaseNumber,
            'CourtName' => $request->CourtName,
            'FilingDate' => $request->FilingDate,
            'OpposingParty' => $request->OpposingParty,
            'CaseType' => $request->CaseType,
            'Status' => $request->Status,
            'Summary' => $request->Summary,
            'AssignedCounselID' => $request->AssignedCounselID,
            'CaseDMSDocID' => $request->CaseDMSDocID,
            'ModifiedBy' => Auth::Id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case updated successfully.');
    }

    public function show($id)
    {
        $case = LegalCase::findOrFail($id);
        $outcomes = LegalCaseOutcome::where('LegalCaseID', $id)->get();
        return view('legal.disputes.show', compact('case', 'outcomes'));
    }

    public function destroy($id)
    {
        $case = LegalCase::findOrFail($id);
        $case->delete();

        return redirect()->route('legal.cases.index')->with('success', 'Legal case deleted successfully.');
    }

}
<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseController extends Controller
{
    public function index()
    {
        $cases = LegalCase::all();
        return view('legal.disputes.index', compact('cases'));
    }

    public function create()
    {
        return view('legal.disputes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CaseTitle'=> 'required|string',
            'CaseNumber'=> 'required|string',
            'CourtName'=> 'required|string',
            'FilingDate'=> 'required|date',
            'OpposingParty'=> 'required|string',
            'CaseType'=> 'required|string',
            'Summary'=> 'required|string',
            'AssignedCounselID'=> 'nullable',
            'CaseDMSDocID'=> 'nullable',

        ]);

        LegalCase::create([
            'CaseTitle' => $validated['CaseTitle'],
            'CaseNumber' => $validated['CaseNumber'],
            'CourtName' => $validated['CourtName'],
            'FilingDate' => $validated['FilingDate'],
            'OpposingParty' => $validated['OpposingParty'],
            'CaseType' => $validated['CaseType'],
            'Summary' => $validated['Summary'],
            'AssignedCounselID' => $validated['AssignedCounselID']??null,
            'CaseDMSDocID' => $validated['CaseDMSDocID'] ?? null,
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case created successfully.');
    }

    public function edit($id)
    {
        $case = LegalCase::findOrFail($id);
        return view('legal.disputes.edit', compact('case'));
    }

    public function update(Request $request, $id)
    {
        $case = LegalCase::findOrFail($id);
        $case->update([
            'CaseTitle' => $request->CaseTitle,
            'CaseNumber' => $request->CaseNumber,
            'CourtName' => $request->CourtName,
            'FilingDate' => $request->FilingDate,
            'OpposingParty' => $request->OpposingParty,
            'CaseType' => $request->CaseType,
            'Status' => $request->Status,
            'Summary' => $request->Summary,
            'AssignedCounselID' => $request->AssignedCounselID,
            'CaseDMSDocID' => $request->CaseDMSDocID,
            'ModifiedBy' => Auth::Id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case updated successfully.');
    }

    public function show($id)
    {
        $case = LegalCase::findOrFail($id);
        $outcomes = LegalCaseOutcome::where('LegalCaseID', $id)->get();
        return view('legal.disputes.show', compact('case', 'outcomes'));
    }

    public function destroy($id)
    {
        $case = LegalCase::findOrFail($id);
        
        $case->delete();

        return redirect()->route('legal.cases.index')->with('success', 'Legal case deleted successfully.');
    }

}
<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseCounsel;
use App\Models\Legal\LegalCaseEvidence;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalCaseController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCase::class);

        $cases = LegalCase::all();
        return view('legal.disputes.index', compact('cases'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCase::class);

        $caseTypes = CodeDetail::select('CodeID', 'Value', 'Description')
            ->where('CodeID', 'CaseTypes')
            ->get();

        return view('legal.disputes.create', compact('caseTypes'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCase::class);

        $validated = $request->validate([
            'CaseTitle'=> 'required|string',
            'CaseNumber'=> 'required|string',
            'CourtName'=> 'required|string',
            'FilingDate'=> 'required|date',
            'OpposingParty'=> 'required|string',
            'CaseType'=> 'required|string',
            'Summary'=> 'required|string',
            'AssignedCounselID'=> 'nullable',
            'CaseDMSDocID'=> 'nullable',

        ]);

        $duplicate = LegalCase::where('CaseNumber', $validated['CaseNumber'])
            ->exists();
        if($duplicate){
            return back()->with('error', 'A legal case with this case number already exists.');
        }

        try{
            DB::beginTransaction();
        
        LegalCase::create([
            'CaseTitle' => $validated['CaseTitle'],
            'CaseNumber' => $validated['CaseNumber'],
            'CourtName' => $validated['CourtName'],
            'FilingDate' => $validated['FilingDate'],
            'OpposingParty' => $validated['OpposingParty'],
            'CaseType' => $validated['CaseType'],
            'Summary' => $validated['Summary'],
            'AssignedCounselID' => $validated['AssignedCounselID']??null,
            'CaseDMSDocID' => $validated['CaseDMSDocID'] ?? null,
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        activity()
            ->performedOn(new LegalCase())
            ->causedBy(Auth::user())
            ->log('Legal case created');

        DB::commit();

        return redirect()->route('legal.cases.index')->with('success', 'Legal case created successfully.');
        }
        catch(\Throwable $th){
            DB::rollBack();
            activity()
                ->performedOn(new LegalCase())
                ->causedBy(Auth::user())
                ->log('Error creating legal case: ' . $th->getMessage());

            Log::error('Error creating legal case: ' . $th->getMessage());
            return back()->with('error', 'Error creating legal case: ' . $th->getMessage());
        }

    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCase::class);

        $case = LegalCase::findOrFail($id);
        $caseTypes = CodeDetail::select('CodeID', 'Value', 'Description')
            ->where('CodeID', 'CaseTypes')
            ->get();

        return view('legal.disputes.edit', compact('case', 'caseTypes'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCase::class);

        try{
        $validated = $request->validate([
            'CaseTitle'=> 'required|string',
            'CaseNumber'=> 'required|string',
            'CourtName'=> 'required|string',
            'FilingDate'=> 'required|date',
            'OpposingParty'=> 'required|string',
            'CaseType'=> 'required|string',
            'Status'=> 'required|string',
            'Summary'=> 'required|string',
            'AssignedCounselID'=> 'nullable',
            'CaseDMSDocID'=> 'nullable',
        ]);

        DB::beginTransaction();

        $case = LegalCase::findOrFail($id);
        $case->update([
            'CaseTitle' => $request->CaseTitle,
            'CaseNumber' => $request->CaseNumber,
            'CourtName' => $request->CourtName,
            'FilingDate' => $request->FilingDate,
            'OpposingParty' => $request->OpposingParty,
            'CaseType' => $request->CaseType,
            'Status' => $request->Status,
            'Summary' => $request->Summary,
            'AssignedCounselID' => $request->AssignedCounselID,
            'CaseDMSDocID' => $request->CaseDMSDocID,
            'ModifiedBy' => Auth::Id(),
            'ModifiedOn' => now(),
        ]);

        activity()
            ->performedOn(new LegalCase())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'update'])
            ->log('Legal case updated');

        DB::commit();

        return redirect()->route('legal.cases.index')->with('success', 'Legal case updated successfully.');
    }catch(\Throwable $th){
        DB::rollBack();
            activity()
                ->performedOn(new LegalCase())
                ->causedBy(Auth::user())
                ->log('Error updating legal case: ' . $th->getMessage());

            Log::error('Error updating legal case: ' . $th->getMessage());
            return back()->with('error', 'Error updating legal case: ' . $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCase::class);

        $case = LegalCase::findOrFail($id);
        $counsels = LegalCaseCounsel::where('LegalCaseID', $id)->get();
        $outcomes = LegalCaseOutcome::where('LegalCaseID', $id)->get();
        return view('legal.disputes.show', compact('case', 'outcomes', 'counsels'));
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationDelete, LegalCase::class);

        try{
        DB::beginTransaction();
        $case = LegalCase::findOrFail($id);
        $caseId = $case->Id;
        $evidence = LegalCaseEvidence::where('LegalCaseID', $caseId)
            ->update(['DeletedBy' => Auth::id()]);
        $evidence = LegalCaseEvidence::where('LegalCaseID', $caseId)->delete();

        $counsel = LegalCaseCounsel::where('LegalCaseID', $caseId)
            ->update(['DeletedBy' => Auth::id()]);
        $counsel = LegalCaseCounsel::where('LegalCaseID', $caseId)->delete();

        $outcome = LegalCaseOutcome::where('LegalCaseID', $caseId)
            ->update(['DeletedBy' => Auth::id()]);
        $outcome = LegalCaseOutcome::where('LegalCaseID', $caseId)->delete();
        
        $case = LegalCase::where('Id', $caseId)
            ->update(['DeletedBy' => Auth::id()]);
        $case = LegalCase::where('Id', $caseId)->delete();
            
        activity()
            ->performedOn(new LegalCase())
            ->causedBy(Auth::user())
            ->log('Legal case deleted');
        DB::commit();
        return back()->with('success', 'Legal case deleted successfully.');
        }
        catch(\Throwable $th){
            DB::rollBack();
            activity()
                ->performedOn(new LegalCase())
                ->causedBy(Auth::user())
                ->log('Error deleting legal case: ' . $th->getMessage());
            Log::error('Error deleting legal case: ' . $th->getMessage());
            return back()->with('error', 'Legal case not found.');
        }

    }

}
