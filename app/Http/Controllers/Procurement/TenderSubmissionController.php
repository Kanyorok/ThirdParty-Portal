<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\BidSubmission;
use Illuminate\Http\Request;
use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Support\Facades\DB;


class TenderSubmissionController extends Controller
{
    public function index()
    {
        $submissions = BidSubmission::with('submissionMode', 'createdByUser')->get();
        return view('procurement.tendering.suppliermanagement.bidsubmission.index', compact('submissions'));
    }

    public function create()
    {
        $tenders = Tender::select('TenderNo')->get();
        $suppliers = Supplier::select('SupplierName')->get();
        $submissionModes = DB::table('t_CodeDetails')
            ->where('CodeID', 'SubmissionMode')
            ->get(['ID', 'Description']);
        return view('procurement.tendering.suppliermanagement.bidsubmission.create', compact('tenders', 'suppliers', 'submissionModes'));
    }
    public function view($Id)
{
    $submission = BidSubmission::findOrFail($Id);
    return view('procurement.tendering.suppliermanagement.bidsubmission.view', compact('submission'));
}

public function edit($Id)
{
    $submission = BidSubmission::findOrFail($Id);
    //return view('procurement.tendering.suppliermanagement.bidsubmission.edit', compact('submission'));
}
public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'tender_ref' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'submission_mode' => 'required|string|max:255',
            'received_at' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
           // 'bid_files' => 'required|file|mimes:zip,pdf|max:10240', // Max 10MB
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('bid_files')) {
            $file = $request->file('bid_files');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bid_documents', $fileName, 'public'); // Store in storage/app/public/bid_documents
        }
// Map submission_mode to t_CodeDetails ID
    $submissionModeId = DB::table('t_CodeDetails')
        ->where('CodeID', 'SubmissionMode')
        ->where('Description', $request->submission_mode)
        ->value('ID');

    if (!$submissionModeId) {
        return redirect()->back()->withErrors(['submission_mode' => 'Invalid submission mode selected.']);
    }

        // Save to database
        BidSubmission::create([
            'TenderRef' => $request->tender_ref,
            'SupplierName' => $request->supplier_name,
            'SubmissionMode' => $submissionModeId,
            'ReceivedAt' => $request->received_at,
            'RecordedBy' => $request->recorded_by,
            'Remarks' => $request->remarks,
            'Documents' => $filePath,
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);

        return redirect()->route('tendersubmission.index')->with('success', 'Submission recorded successfully.');
    }
}
