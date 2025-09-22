<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalCaseEvidence;
use App\Models\Legal\LegalCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalCaseEvidenceController extends Controller
{
    public function index($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseEvidence::class);

        $case = LegalCase::findOrFail($caseId);
        $evidence = LegalCaseEvidence::where('LegalCaseID', $caseId)->get();

        return view('legal.disputes.evidence.index', compact('case', 'evidence'));
    }

    public function create($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseEvidence::class);

        $case = LegalCase::findOrFail($caseId);
        return view('legal.disputes.evidence.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseEvidence::class);

        $validated = $request->validate([
            'EvidenceTitle' => 'required|string',
            'Description' => 'required|string',
            'DMSDocumentID' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg',
            'ExternalLink' => 'nullable|url'
        ], [
            'DMSDocumentID.mimes' => 'Only PDF, Word, Excel, CSV, JPG, and PNG files are allowed.',
            'DMSDocumentID.max' => 'File size must not exceed 5 MB.',
        ]);

        try {
            $fileName = $request->hasFile('DMSDocumentID')
                ? $request->file('DMSDocumentID')->getClientOriginalName()
                : null;

            $duplicate = LegalCaseEvidence::where('LegalCaseID', $caseId)
                ->where('EvidenceTitle', $validated['EvidenceTitle'])
                ->where('DMSDocumentID', $fileName)
                ->where('ExternalLink', $validated['ExternalLink'])
                ->exists();

            if ($duplicate) {
                return back()->with('error', 'Duplicate Evidence entry detected. Please modify your input.');
            }

            DB::beginTransaction();
            $evidence = LegalCaseEvidence::create([
                'LegalCaseID' => $caseId,
                'EvidenceTitle' => $validated['EvidenceTitle'],
                'Description' => $validated['Description'],
                'DMSDocumentID' => $request->hasFile('DMSDocumentID')
                    ? $request->file('DMSDocumentID')->getClientOriginalName() : null,
                'ExternalLink' => $validated['ExternalLink'] ?? null,
                'IsActive' => $validated['IsActive'] ?? 'Active', // Default to inactive
                'UploadedBy' => Auth::id(),
                'UploadedOn' => now(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            //Upload Evidence to E-DMS
            if ($request->hasFile('DMSDocumentID')) {
                $evidence->newDocument(
                    ModulesEnum::Legal, // or ModulesEnum::INVOICE if you have it
                    $request->file('DMSDocumentID'),
                    [PermissionEnum::ContractCreate], // Permissions
                    Auth::user()
                );
            }

            activity()
                ->performedOn($evidence)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Uploaded Evidence:' . $evidence->EvidenceTitle);

            DB::commit();
            return redirect()->route('legal.cases.evidence.index', $caseId)
                ->with('success', 'Evidence linked successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create Evidence: ' . $th->getMessage());
            return back()->with('error', 'An Error Occurred. Please try again');
        }
    }

    public function show($case_id, $evidence_id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseEvidence::class);

        $evidence = LegalCaseEvidence::with('case:Id,CaseTitle,CaseNumber')->findOrFail($evidence_id);

        return view('legal.disputes.evidence.show', compact('evidence'));
    }

    public function edit($case, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseEvidence::class);

        $cases = LegalCase::findOrFail($case);
        $evidence = LegalCaseEvidence::where('LegalCaseID', $case)->findOrFail($id);

        return view('legal.disputes.evidence.edit', compact('cases', 'evidence'));
    }

    public function update(Request $request, $caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseEvidence::class);
        try {
            DB::beginTransaction();

            $evidence = LegalCaseEvidence::findOrFail($id);

            $validated = $request->validate([
                'EvidenceTitle' => 'required|string',
                'Description' => 'required|string',
                'DMSDocumentID' => 'nullable|string',
                'ExternalLink' => 'nullable|url',
                'IsActive' => 'string'
            ]);

            $evidence->update([
                'EvidenceTitle' => $validated['EvidenceTitle'],
                'Description' => $validated['Description'],
                'DMSDocumentID' => $validated['DMSDocumentID'] ?? null,
                'ExternalLink' => $validated['ExternalLink'] ?? null,
                'IsActive' => $request->has('IsActive') ? 'Active' : 'Inactive',
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            activity()
                ->performedOn(new LegalCaseEvidence())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Update'])
                ->log('Updated Evidence' . $evidence->EvidenceTitle);

            DB::commit();

            return redirect()->route('legal.cases.evidence.index', $caseId)
                ->with('success', 'Evidence updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete: ' . $th->getMessage());
            return back()->with('error', 'An Error Occurred. Please try again');
        }
    }

    public function destroy($caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationDelete, LegalCaseEvidence::class);
        try {
            DB::beginTransaction();

            $evidence = LegalCaseEvidence::findOrFail($id);
            $evidence->DeletedBy = Auth::id();
            $evidence->save();
            $evidence->delete();

            activity()
                ->performedOn(new LegalCaseEvidence())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted Evidence');

            DB::commit();
            return redirect()->route('legal.cases.evidence.index', $caseId)
                ->with('success', 'Evidence deleted successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete Evidence: ' . $th->getMessage());
            return back()->with('error', 'An Error Occurred. Please try again');
        }

    }
}

