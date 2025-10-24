<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceObligation;
use App\Models\Legal\ComplianceObligationDocument;
use App\Models\Legal\ComplianceObligationImpact;
use App\Models\Legal\RegulatoryBody;
use App\Models\Legal\ComplianceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceObligationController extends Controller
{
    public function index()
    {
        $obligations = ComplianceObligation::with(['regulator', 'area'])->orderBy('CreatedOn', 'desc')->get();
        return view('legal.compliance.obligations.index', compact('obligations'));
    }

    public function create()
    {
        $regulators = RegulatoryBody::orderBy('Name')->pluck('Name', 'Id');
        $areas = ComplianceArea::orderBy('Name')->pluck('Name', 'Id');
        return view('legal.compliance.obligations.create', compact('regulators', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'RegulatorID' => 'required|exists:t_RegulatoryBodies,Id',
            'ComplianceAreaID' => 'required|exists:t_ComplianceAreas,Id',
            'EffectiveDate' => 'nullable|date',
        ]);

        $obligation = ComplianceObligation::create($validated + [
                'IsActive' => 1,
                'CreatedBy' => auth()->id() ?? 1,
                'CreatedOn' => now(),
            ]);

        return redirect()->route('legal.compliance.obligations.show', $obligation->Id)
            ->with('success', 'Obligation created successfully.');
    }

    public function show($id)
    {
        $obligation = ComplianceObligation::with(['regulator', 'area', 'documents', 'impacts'])->findOrFail($id);
        return view('legal.compliance.obligations.show', compact('obligation'));
    }

    public function edit($id)
    {
        $obligation = ComplianceObligation::findOrFail($id);
        $regulators = RegulatoryBody::orderBy('Name')->pluck('Name', 'Id');
        $areas = ComplianceArea::orderBy('Name')->pluck('Name', 'Id');
        return view('legal.compliance.obligations.edit', compact('obligation', 'regulators', 'areas'));
    }

    public function update(Request $request, $id)
    {
        $obligation = ComplianceObligation::findOrFail($id);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'RegulatorID' => 'required|exists:t_RegulatoryBodies,Id',
            'ComplianceAreaID' => 'required|exists:t_ComplianceAreas,Id',
            'EffectiveDate' => 'nullable|date',
            'IsActive' => 'nullable|boolean',
        ]);

        $obligation->update($validated + [
                'ModifiedBy' => auth()->id() ?? 1,
                'ModifiedOn' => now(),
            ]);

        return redirect()->route('legal.compliance.obligations.show', $id)
            ->with('success', 'Obligation updated successfully.');
    }

    public function destroy($id)
    {
        $obligation = ComplianceObligation::findOrFail($id);
        $obligation->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id() ?? 1,
            'DeletedOn' => now(),
        ]);
        return redirect()->route('legal.compliance.obligations.index')
            ->with('success', 'Obligation deactivated.');
    }

    public function uploadDoc(Request $request, $id)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xlsx,xls,ppt,pptx',
        ]);

        $obligation = ComplianceObligation::findOrFail($id);
        $file = $request->file('document');

        // Version control: increment version
        $latestVersion = ComplianceObligationDocument::where('ObligationID', $id)->max('Version') ?? 0;
        $version = $latestVersion + 1;

        $path = $file->store('compliance/obligations');

        ComplianceObligationDocument::create([
            'ObligationID' => $id,
            'FileName' => $file->getClientOriginalName(),
            'MimeType' => $file->getMimeType(),
            'FilePath' => $path,
            'Version' => $version,
            'UploadedBy' => auth()->id() ?? 1,
            'UploadedOn' => now(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function addImpact(Request $request, $id)
    {
        $request->validate([
            'ImpactDescription' => 'required|string',
            'Department' => 'nullable|string|max:150',
        ]);

        ComplianceObligationImpact::create([
            'ObligationID' => $id,
            'ImpactDescription' => $request->ImpactDescription,
            'Department' => $request->Department,
            'AssessedBy' => auth()->id() ?? 1,
            'AssessedOn' => now(),
        ]);

        return back()->with('success', 'Impact assessment added.');
    }
}
