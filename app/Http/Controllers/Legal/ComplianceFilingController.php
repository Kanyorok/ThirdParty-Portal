<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceFilingTemplate;
use App\Models\Legal\ComplianceFiling;
use App\Models\Legal\ComplianceFilingAcknowledgment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplianceFilingController extends Controller
{
    // List all filings
    public function index()
    {
        $filings = ComplianceFiling::with(['template'])->orderBy('SubmissionDate','desc')->get();
        return view('legal.compliance.filings.index', compact('filings'));
    }

    // Show filing detail
    public function show($id)
    {
        $filing = ComplianceFiling::with(['template','acknowledgments'])->findOrFail($id);
        return view('legal.compliance.filings.show', compact('filing'));
    }

    // Filing create form
    public function create()
    {
        $templates = ComplianceFilingTemplate::pluck('Name','Id');
        return view('legal.compliance.filings.create', compact('templates'));
    }

    // Store filing
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:200',
            'FilingTypeID' => 'required|exists:t_FilingTypes,Id',
            'RegulatorID' => 'required|exists:t_RegulatoryBodies,Id',
            'FormatID' => 'nullable|exists:t_FileFormats,Id',
            'Frequency' => 'nullable|string|max:50',
            'DueDay' => 'nullable|string|max:50',
            'PortalURL' => 'nullable|url|max:500',
            'Description' => 'nullable|string',
        ]);

        $fileName = null; $mime = null; $path = null;
        if($request->hasFile('File')){
            $file = $request->file('File');
            $fileName = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->store('compliance/filings');
        }

        ComplianceFiling::create([
            'TemplateID' => $request->TemplateID,
            'SubmissionDate' => $request->SubmissionDate,
            'FileName' => $fileName,
            'MimeType' => $mime,
            'FilePath' => $path,
            'Notes' => $request->Notes,
            'SubmittedBy' => auth()->id() ?? 1,
            'Status' => 'Submitted',
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.filings.index')->with('success','Filing submitted successfully.');
    }

    // Template list
    public function templates()
    {
        $templates = ComplianceFilingTemplate::with(['regulator','type','format'])->get();
        return view('legal.compliance.filings.templates', compact('templates'));
    }

    // Template create form
    public function createTemplate()
    {
        $types = DB::table('t_FilingTypes')->pluck('Name','Id');
        $regulators = DB::table('t_RegulatoryBodies')->pluck('Name','Id');
        $formats = DB::table('t_FileFormats')->pluck('Name','Id');
        return view('legal.compliance.filings.create-template', compact('types','regulators','formats'));
    }

    // Store template
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:200',
            'FilingTypeID' => 'required|exists:t_FilingTypes,Id',
            'RegulatorID' => 'required|exists:t_RegulatoryBodies,Id',
            'FormatID' => 'nullable|exists:t_FileFormats,Id',
            'Frequency' => 'nullable|string|max:50',
            'DueDay' => 'nullable|string|max:50',
            'Description' => 'nullable|string',
        ]);

        ComplianceFilingTemplate::create($validated + [
            'IsActive' => 1,
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.filings.templates')->with('success','Template created successfully.');
    }

    // Upload acknowledgment
    public function uploadAck(Request $request, $id)
    {
        $request->validate([
            'AckFile' => 'required|file|mimes:pdf,doc,docx',
        ]);

        $filing = ComplianceFiling::findOrFail($id);
        $file = $request->file('AckFile');
        $path = $file->store('compliance/acknowledgments');

        ComplianceFilingAcknowledgment::create([
            'FilingID' => $id,
            'AckFileName' => $file->getClientOriginalName(),
            'MimeType' => $file->getMimeType(),
            'FilePath' => $path,
            'UploadedBy' => auth()->id() ?? 1,
            'UploadedOn' => now(),
        ]);

        return back()->with('success','Acknowledgment uploaded successfully.');
    }
}
