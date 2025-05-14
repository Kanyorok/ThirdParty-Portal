<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BidSubmission;

class TenderSubmissionController extends Controller
{
    public function index()
    {
        $submissions = BidSubmission::all(); // Fetch all records from bid_submissions table
        return view('procurement.tendering.suppliermanagement.bidsubmission.index', compact('submissions'));
    }

    public function create()
    {
        return view('procurement.tendering.suppliermanagement.bidsubmission.create');
    }
    public function view($Id)
{
    $submission = BidSubmission::findOrFail($Id);
    return view('procurement.tendering.suppliermanagement.bidsubmission.view', compact('submission'));
}

public function edit($Id)
{
    $submission = BidSubmission::findOrFail($Id);
    return view('procurement.tendering.suppliermanagement.bidsubmission.edit', compact('submission'));
}
public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'tender_ref' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'submission_mode' => 'required|in:Hand delivered,Courier,Email,Other',
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

        // Save to database
        BidSubmission::create([
            'TenderRef' => $request->tender_ref,
            'SupplierName' => $request->supplier_name,
            'SubmissionMode' => $request->submission_mode,
            'ReceivedAt' => $request->received_at,
            'RecordedBy' => $request->recorded_by,
            'Remarks' => $request->remarks,
            'Documents' => $filePath,
        ]);

        return redirect()->route('tendersubmission.index')->with('success', 'Submission recorded successfully.');
    }
}
